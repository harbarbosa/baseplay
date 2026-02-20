<?php

namespace App\Controllers;

use Config\Services;

class Auth extends BaseController
{
    public function loginForm()
    {
        if (Services::auth()->isLoggedIn()) {
            return redirect()->to('/');
        }

        return view('auth/login');
    }

    public function login()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->login, config('Validation')->login_errors);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        if (! Services::auth()->attemptLogin($email, $password)) {
            return redirect()->back()->withInput()->with('error', 'Credenciais inválidas.');
        }

        Services::audit()->log(session('user_id'), 'login');

        return redirect()->to('/');
    }

    public function logout()
    {
        Services::audit()->log(session('user_id'), 'logout');
        Services::auth()->logout();

        return redirect()->to('/login');
    }
}
