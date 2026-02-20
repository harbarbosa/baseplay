<?php

namespace App\Controllers\Api;

use App\Services\AthleteGuardianService;

class AthleteGuardiansController extends BaseApiController
{
    protected AthleteGuardianService $links;

    public function __construct()
    {
        $this->links = new AthleteGuardianService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ])->setStatusCode($code);
    }

    protected function fail(string $message, int $code = 400, $errors = null)
    {
        return service('response')->setJSON([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ])->setStatusCode($code);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('guardians.update')) {
            return $response;
        }

        $link = $this->links->findLink($id);
        if (!$link) {
            return $this->fail('Vf¯,¿,½nculo nf¯,¿,½o encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $isPrimary = (int) ($payload['is_primary'] ?? 0);
        $notes = $payload['notes'] ?? null;

        $this->links->updateLink($id, (int) $link['athlete_id'], $isPrimary, $notes);
        return $this->ok(['id' => $id], 'Vf¯,¿,½nculo atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('guardians.delete')) {
            return $response;
        }

        $link = $this->links->findLink($id);
        if (!$link) {
            return $this->fail('Vf¯,¿,½nculo nf¯,¿,½o encontrado.', 404);
        }

        $this->links->unlink($id);
        return $this->ok(['id' => $id], 'Vf¯,¿,½nculo removido.');
    }
}
