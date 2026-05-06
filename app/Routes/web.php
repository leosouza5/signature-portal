<?php

use App\Controllers\AuthController;
use App\Controllers\DocumentController;

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/', [DocumentController::class, 'dashboard'], ['auth']);

$router->get('/dashboard', [DocumentController::class, 'dashboard'], ['auth']);

$router->get('/documentos', [DocumentController::class, 'documents'], ['auth']);
$router->get('/documentos/criar', [DocumentController::class, 'createForm'], ['auth']);
$router->post('/documentos', [DocumentController::class, 'store'], ['auth']);
$router->get('/documentos/{id}', [DocumentController::class, 'show'], ['auth']);
$router->post('/documentos/{id}/atualizar-status', [DocumentController::class, 'refreshStatus'], ['auth']);
$router->post('/documentos/{id}/download', [DocumentController::class, 'download'], ['auth']);
