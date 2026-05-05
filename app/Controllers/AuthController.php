<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    public function loginForm(): void
    {
        view('login');
    }
    public function registerForm(): void
    {
        view('register');
    }

    public function login(): void
    {
        try {
            $service = new AuthService();

            $user = $service->login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');

            $_SESSION['user_id'] = $user['id'];

            redirect('/dashboard');
        } catch (\Exception $exception) {
            flash_old(['email' => $_POST['email'] ?? '']);
            flash('error', $exception->getMessage());
            redirect('/login');
        }
    }



    public function register(): void
    {
        try {
            if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {

                throw new \Exception('A confirmacao de senha nao confere.');
            }

            $service = new AuthService();
            $userId = $service->register(
                trim($_POST['name'] ?? ''),
                trim($_POST['email'] ?? ''),
                $_POST['password'] ?? ''
            );

            $_SESSION['user_id'] = $userId;
            redirect('/dashboard');
        } catch (\Exception $exception) {
            flash_old(['name' => $_POST['name'] ?? '', 'email' => $_POST['email'] ?? '']);
            flash('error', $exception->getMessage());

            redirect('/register');
        }
    }

    public function logout(): void
    {
        session_destroy();
        redirect('/login');
    }
}
