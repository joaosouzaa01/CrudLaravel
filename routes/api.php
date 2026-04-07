<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);       // Login

Route::middleware('auth:sanctum')->group(function () {
    // Retorna o usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // CRUD completo para usuários
    Route::get('/users', [AuthController::class, 'index']);        // Listar todos
    Route::get('/users/{id}', [AuthController::class, 'show']);    // Mostrar 1 usuário
    Route::put('/users/{id}', [AuthController::class, 'update']);  // Atualizar usuário
    Route::delete('/users/{id}', [AuthController::class, 'destroy']); // Deletar usuário

});

// Rotas públicas (sem autenticação)
Route::post('/user_create', [AuthController::class, 'store']); // Criar usuário
