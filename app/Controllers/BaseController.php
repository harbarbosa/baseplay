<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected array $scopedTeamIds = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url', 'auth', 'date', 'enum'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        $this->scopedTeamIds = $this->resolveScopedTeamIds();
    }

    protected function resolveScopedTeamIds(): array
    {
        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return [];
        }

        if (function_exists('has_permission') && has_permission('admin.access')) {
            return [];
        }

        $rows = db_connect()->table('user_team_links')
            ->select('team_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_map(static fn($row) => (int) $row['team_id'], $rows);
    }

    protected function pickScopedTeamId(?int $teamId): ?int
    {
        if ($this->scopedTeamIds === []) {
            return $teamId;
        }

        if ($teamId && in_array($teamId, $this->scopedTeamIds, true)) {
            return $teamId;
        }

        return $this->scopedTeamIds[0] ?? null;
    }

    protected function denyIfTeamForbidden(?int $teamId, string $redirectTo = '/')
    {
        if ($this->scopedTeamIds === []) {
            return null;
        }

        if (!$teamId || !in_array($teamId, $this->scopedTeamIds, true)) {
            return redirect()->to($redirectTo)->with('error', 'Acesso negado.');
        }

        return null;
    }
}
