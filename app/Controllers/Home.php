<?php

namespace App\Controllers;

use App\Models\User;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function saveToken()
    {
        $data = $this->request->getJSON(true);
        $token = $data['token'] ?? null;
        if (!$token) {
            return $this->response->setJSON(['error' => 'Token is missing'])->setStatusCode(400);
        }

        $userModel = new User();
        $email = session()->get('email');

        if (!$email) {
            return $this->response->setJSON(['error' => 'Email not found'])->setStatusCode(404);
        }

        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(404);
        }

        $userModel->update($user['id'], ['device_token' => $token]);

        return $this->response->setJSON(['status' => 'Token saved', 'token' => $token])->setStatusCode(200);


    }
}
