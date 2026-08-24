<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proprietario\StoreProprietarioRequest;
use App\Http\Requests\Proprietario\UpdateProprietarioRequest;
use App\Models\Proprietario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProprietarioController extends Controller
{
    #[OA\Get(
        path: '/api/proprietarios/busca',
        tags: ['Proprietários'],
        summary: 'Busca rápida de proprietários para autocomplete',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string'), description: 'Termo de busca: nome, CPF, e-mail ou telefone'),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de proprietários encontrados'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function busca(Request $request): JsonResponse
    {
        $termo = trim($request->query('q', ''));
        $limit = min((int) $request->query('limit', 10), 50);

        if ($termo === '') {
            return response()->json([]);
        }

        $proprietarios = Proprietario::where(function ($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
                ->orWhere('cpf', 'like', "%{$termo}%")
                ->orWhere('email', 'like', "%{$termo}%")
                ->orWhere('telefone', 'like', "%{$termo}%");
        })
            ->orderBy('nome')
            ->limit($limit)
            ->get(['idproprietario', 'nome', 'cpf', 'email', 'telefone']);

        return response()->json($proprietarios);
    }

    #[OA\Get(
        path: '/api/proprietarios',
        tags: ['Proprietários'],
        summary: 'Listar todos os proprietários',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Busca por nome, CPF ou cidade'),
            new OA\Parameter(name: 'cidade', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filtrar por cidade'),
            new OA\Parameter(name: 'estado', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filtrar por estado (UF)'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de proprietários'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Proprietario::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        if ($cidade = $request->query('cidade')) {
            $query->where('cidade', 'like', "%{$cidade}%");
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        $proprietarios = $query->orderBy('nome')->paginate($request->query('per_page', 15));

        return response()->json($proprietarios);
    }

    #[OA\Post(
        path: '/api/proprietarios',
        tags: ['Proprietários'],
        summary: 'Criar um proprietário',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nome', 'cpf'],
                properties: [
                    new OA\Property(property: 'nome', type: 'string', example: 'João da Silva'),
                    new OA\Property(property: 'cpf', type: 'string', example: '12345678900'),
                    new OA\Property(property: 'email', type: 'string', example: 'joao@email.com'),
                    new OA\Property(property: 'telefone', type: 'string', example: '11987654321'),
                    new OA\Property(property: 'logradouro', type: 'string', example: 'Rua das Flores'),
                    new OA\Property(property: 'numero', type: 'integer', example: 42),
                    new OA\Property(property: 'bairro', type: 'string', example: 'Centro'),
                    new OA\Property(property: 'cidade', type: 'string', example: 'São Paulo'),
                    new OA\Property(property: 'estado', type: 'string', example: 'SP'),
                    new OA\Property(property: 'cep', type: 'string', example: '01310100'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Proprietário criado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function store(StoreProprietarioRequest $request): JsonResponse
    {
        $proprietario = Proprietario::create($request->validated());

        return response()->json($proprietario, 201);
    }

    #[OA\Get(
        path: '/api/proprietarios/{id}',
        tags: ['Proprietários'],
        summary: 'Exibir um proprietário',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dados do proprietário'),
            new OA\Response(response: 404, description: 'Proprietário não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $proprietario = Proprietario::withCount('imoveis')->findOrFail($id);

        return response()->json($proprietario);
    }

    #[OA\Put(
        path: '/api/proprietarios/{id}',
        tags: ['Proprietários'],
        summary: 'Atualizar um proprietário',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'nome', type: 'string'),
                new OA\Property(property: 'cpf', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'telefone', type: 'string'),
                new OA\Property(property: 'cidade', type: 'string'),
                new OA\Property(property: 'estado', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Proprietário atualizado'),
            new OA\Response(response: 404, description: 'Proprietário não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function update(UpdateProprietarioRequest $request, int $id): JsonResponse
    {
        $proprietario = Proprietario::findOrFail($id);
        $proprietario->update($request->validated());

        return response()->json($proprietario);
    }

    #[OA\Delete(
        path: '/api/proprietarios/{id}',
        tags: ['Proprietários'],
        summary: 'Remover um proprietário (soft delete)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Proprietário removido'),
            new OA\Response(response: 404, description: 'Proprietário não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $proprietario = Proprietario::findOrFail($id);
        $proprietario->delete();

        return response()->json(['message' => 'Proprietário removido com sucesso.']);
    }
}
