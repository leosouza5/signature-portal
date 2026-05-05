<?php

use App\Controllers\AuthController;
use App\Controllers\EnvelopeController;

$router->get('/', [EnvelopeController::class, 'dashboard']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [EnvelopeController::class, 'dashboard']);
$router->get('/documents', [EnvelopeController::class, 'documents']);
$router->get('/envelopes/create', [EnvelopeController::class, 'createForm']);
$router->post('/envelopes', [EnvelopeController::class, 'store']);
$router->get('/envelopes/{id}', [EnvelopeController::class, 'show']);
$router->post('/envelopes/{id}/refresh-status', [EnvelopeController::class, 'refreshStatus']);
$router->post('/envelopes/{id}/download', [EnvelopeController::class, 'download']);
