<?php

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function register(string $name, string $email, string $password): int
    {
        if ($name === '' || $email === '' || $password === '') {
            throw new \Exception('Preencha todos os campos.');
        }

        if ($this->users->findByEmail($email)) {
            throw new \Exception('Este e-mail ja esta cadastrado.');
        }

        return $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
    }

    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \Exception('E-mail ou senha invalidos.');
        }

        return $user;
    }
}
