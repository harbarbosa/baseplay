<?php

namespace App\Controllers\Api;

use App\Services\DocumentAlertService;
use App\Models\GuardianModel;
use App\Models\UserModel;

class DocumentAlertsController extends BaseApiController
{
    protected DocumentAlertService $alerts;

    public function __construct()
    {
        $this->alerts = new DocumentAlertService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ])->setStatusCode($code);
    }

    public function index()
    {
        if ($response = $this->ensurePermission('documents.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->ok(['expired' => [], 'expiring' => [], 'generated_at' => date('Y-m-d H:i:s')]);
        }

        $filters = [];
        $userId = (int) ($user['id'] ?? 0);
        $roles = $this->apiUserRoles($user);

        if (in_array('atleta', $roles, true)) {
            $dbUser = (new UserModel())->find($userId);
            if (!empty($dbUser['athlete_id'])) {
                $filters['athlete_id'] = (int) $dbUser['athlete_id'];
            }
        } elseif (in_array('responsavel', $roles, true) || in_array('responsável', $roles, true)) {
            $guardian = null;
            if (!empty($user['email'])) {
                $guardian = (new GuardianModel())
                    ->where('deleted_at', null)
                    ->where('email', $user['email'])
                    ->first();
            }
            if (!empty($guardian['id'])) {
                $rows = db_connect()->table('athlete_guardians')
                    ->select('athlete_id')
                    ->where('guardian_id', (int) $guardian['id'])
                    ->get()
                    ->getResultArray();
                $filters['athlete_ids'] = array_values(array_filter(array_map(
                    static fn(array $row): int => (int) $row['athlete_id'],
                    $rows
                )));
            }
        }

        $data = $this->alerts->getAlerts([7, 15, 30], $filters);
        return $this->ok($data);
    }
}
