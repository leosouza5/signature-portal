<?php

namespace App\Controllers;

use App\Http\Response;
use App\Http\Session;
use App\Services\AuthService;

class AuthController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function loginForm(): void
    {
        Response::view('auth/login');
    }

    public function registerForm(): void
    {
        Response::view('auth/register');
    }

    public function login(): void
    {
        try {
            $service = new AuthService();

            $user = $service->login(trim($_POST['email']), $_POST['password']);

            $this->session->setUserId($user['id']);

            Response::redirect('/dashboard');
        } catch (\Exception $e) {
            $this->session->saveForm(['email' => $_POST['email']]);
            $this->session->setMessage('error', $e->getMessage());
            Response::redirect('/login');
        }
    }

    public function register(): void
    {
        try {
            if ($_POST['password'] !== $_POST['password_confirmation']) {
                throw new \Exception('As senhas estão diferentes.');
            }

            $service = new AuthService();
            $userId = $service->register(
                trim($_POST['name']),
                trim($_POST['email']),
                $_POST['password']
            );

            $this->session->setUserId($userId);
            Response::redirect('/dashboard');
        } catch (\Exception $e) {
            
            $this->session->saveForm(['name' => $_POST['name'], 'email' => $_POST['email']]);
            $this->session->setMessage('error', $e->getMessage());

            Response::redirect('/register');
        }
    }

    public function logout(): void
    {
        session_destroy();
        Response::redirect('/login');
    }
}
