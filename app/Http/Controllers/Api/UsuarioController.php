<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UsuarioController extends Controller
{
    #[OA\Get(
        path: '/api/usuarios',
        tags: ['Usuários'],
        summary: 'Listar todos os usuários',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Busca por nome, CPF ou e-mail'),
            new OA\Parameter(name: 'cartorio_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filtrar por cartório'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de usuários'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = User::with('cartorio');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($cartorioId = $request->query('cartorio_id')) {
            $query->where('cartorio_id', $cartorioId);
        }

        $usuarios = $query->orderBy('nome')->paginate($request->query('per_page', 15));

        return response()->json($usuarios);
    }

    #[OA\Post(
        path: '/api/usuarios',
        tags: ['Usuários'],
        summary: 'Criar um usuário',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nome', 'cpf', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'nome', type: 'string', example: 'João Silva'),
                    new OA\Property(property: 'cpf', type: 'string', example: '123.456.789-00'),
                    new OA\Property(property: 'email', type: 'string', example: 'joao@siao.com.br'),
                    new OA\Property(property: 'password', type: 'string', example: 'senha1234'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'senha1234'),
                    new OA\Property(property: 'telefone', type: 'string', example: '(11) 91234-5678'),
                    new OA\Property(property: 'endereco', type: 'string', example: 'Rua A, 10'),
                    new OA\Property(property: 'cidade', type: 'string', example: 'São Paulo'),
                    new OA\Property(property: 'estado', type: 'string', example: 'SP'),
                    new OA\Property(property: 'cep', type: 'string', example: '01310-100'),
                    new OA\Property(property: 'cartorio_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuário criado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = User::create($request->validated());

        return response()->json($usuario->load('cartorio'), 201);
    }

    #[OA\Get(
        path: '/api/usuarios/{id}',
        tags: ['Usuários'],
        summary: 'Exibir um usuário',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dados do usuário'),
            new OA\Response(response: 404, description: 'Usuário não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $usuario = User::with('cartorio', 'imoveis')->findOrFail($id);

        return response()->json($usuario);
    }

    #[OA\Put(
        path: '/api/usuarios/{id}',
        tags: ['Usuários'],
        summary: 'Atualizar um usuário',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'nome', type: 'string'),
                new OA\Property(property: 'telefone', type: 'string'),
                new OA\Property(property: 'cidade', type: 'string'),
                new OA\Property(property: 'estado', type: 'string'),
                new OA\Property(property: 'cartorio_id', type: 'integer'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuário atualizado'),
            new OA\Response(response: 404, description: 'Usuário não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function update(UpdateUsuarioRequest $request, int $id): JsonResponse
    {
        $usuario = User::findOrFail($id);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        return response()->json($usuario->load('cartorio'));
    }

    #[OA\Delete(
        path: '/api/usuarios/{id}',
        tags: ['Usuários'],
        summary: 'Remover um usuário (soft delete)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuário removido'),
            new OA\Response(response: 404, description: 'Usuário não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }
}
