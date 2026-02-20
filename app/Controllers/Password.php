<?php

namespace App\Controllers;

use Config\Services;

class Password extends BaseController
{
    public function forgotForm()
    {
        return view('auth/forgot');
    }

    public function sendReset()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->passwordRequest, config('Validation')->passwordRequest_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $email = $this->request->getPost('email');
        $token = Services::auth()->createPasswordReset($email);

        if ($token) {
            Services::audit()->log(null, 'password_reset_requested', ['email' => $email]);
            $resetUrl = base_url('password/reset/' . $token);
            return redirect()->back()->with('success', 'Se o e-mail existir, você pode redefinir por aqui: ' . $resetUrl);
        }

        return redirect()->back()->with('success', 'Se o e-mail existir, enviaremos instruções.');
    }

    public function resetForm(string $token)
    {
        return view('auth/reset', ['token' => $token]);
    }

    public function reset()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->passwordReset, config('Validation')->passwordReset_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        if (!Services::auth()->resetPassword($token, $password)) {
            return redirect()->back()->withInput()->with('error', 'Token inválido ou expirado.');
        }

        Services::audit()->log(null, 'password_reset_completed');

        return redirect()->to('/login')->with('success', 'Senha redefinida com sucesso.');
    }
}
