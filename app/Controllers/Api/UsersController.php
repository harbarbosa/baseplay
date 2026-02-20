<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

class UsersController extends BaseApiController
{
    public function index()
    {
        if ($response = $this->ensurePermission('users.manage')) {
            return $response;
        }

        $userModel = new UserModel();
        $users = $userModel->paginate(15, 'users');
        $pager = $userModel->pager;

        return $this->success([
            'items' => $users,
            'pager' => [
                'currentPage' => $pager->getCurrentPage('users'),
                'pageCount'   => $pager->getPageCount('users'),
                'perPage'     => $pager->getPerPage('users'),
                'total'       => $pager->getTotal('users'),
            ],
        ]);
    }
}
