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
$router->get('/documentos/criar', [EnvelopeController::class, 'createForm']);
$router->post('/documentos', [EnvelopeController::class, 'store']);
$router->get('/documentos/{id}', [EnvelopeController::class, 'show']);
$router->post('/documentos/{id}/atualizar-status', [EnvelopeController::class, 'refreshStatus']);
$router->post('/documents/{id}/download', [EnvelopeController::class, 'download']);
