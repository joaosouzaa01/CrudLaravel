<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Listar todos os usuários.
     */
    public function index()
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'data' => $users->makeHidden(['password', 'remember_token']), // não retorna senha
        ], 200);
    }

    /**
     * Criar um novo usuário.
     */
    public function store(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'nomestring' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-zÀ-ú\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'], // opcional, default 123456
        ], [
            'nomestring.required' => 'O campo nome é obrigatório.',
            'nomestring.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'nomestring.max' => 'O nome deve ter no máximo 50 caracteres.',
            'nomestring.regex' => 'O nome só pode conter letras e espaços.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email precisa ser válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        // Criar ou pegar usuário
        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['nomestring'],
                'password' => isset($validated['password']) ? Hash::make($validated['password']) : Hash::make('123456'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $user->wasRecentlyCreated ? 'Usuário criado com sucesso!' : 'Usuário já existe.',
            'data' => $user->makeHidden(['password', 'remember_token']),
        ], 201);
    }

    /**
     * Mostrar um usuário específico.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user->makeHidden(['password', 'remember_token']),
        ], 200);
    }

    /**
     * Atualizar um usuário.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $validated = $request->validate([
            'nomestring' => ['sometimes', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-zÀ-ú\s]+$/'],
            'email' => ['sometimes', 'string', 'email', 'max:255', "unique:users,email,{$id}"],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'nomestring.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'nomestring.max' => 'O nome deve ter no máximo 50 caracteres.',
            'nomestring.regex' => 'O nome só pode conter letras e espaços.',
            'email.email' => 'O email precisa ser válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        if (isset($validated['nomestring'])) $user->name = $validated['nomestring'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['password'])) $user->password = Hash::make($validated['password']);

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso!',
            'data' => $user->makeHidden(['password', 'remember_token']),
        ], 200);
    }

    /**
     * Deletar um usuário.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário deletado com sucesso!',
        ], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas.'
            ], 401);
        }

        $user = Auth::user();

        // Criar token de API
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'data' => [
                'user' => $user->makeHidden(['password', 'remember_token']),
                'token' => $token
            ]
        ], 200);
    }
}


