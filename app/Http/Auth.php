<?php

namespace App\Http;

class Auth
{
    public function __construct(private Session $session) {}

    public function userId(): int
    {
        return $this->session->getUserId();
    }

    public function requireLogin(): void
    {
        if (!$this->session->getUserId()) {
            Response::redirect('/login');
        }
    }
}
