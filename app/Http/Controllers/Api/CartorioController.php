<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cartorio\StoreCartorioRequest;
use App\Http\Requests\Cartorio\UpdateCartorioRequest;
use App\Models\Cartorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CartorioController extends Controller
{
    #[OA\Get(
        path: '/api/cartorios',
        tags: ['Cartórios'],
        summary: 'Listar todos os cartórios',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Busca por nome ou CNPJ', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Itens por página', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de cartórios'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Cartorio::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%")
                    ->orWhere('cidade', 'like', "%{$search}%");
            });
        }
        if ($request->input('delete')) {
            $query = $query->withTrashed();
        }

        $cartorios = $query->orderBy('nome')->paginate($request->query('per_page', 15));

        return response()->json($cartorios);
    }

    #[OA\Post(
        path: '/api/cartorios',
        tags: ['Cartórios'],
        summary: 'Criar um cartório',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nome', 'cnpj'],
                properties: [
                    new OA\Property(property: 'nome', type: 'string', example: 'Cartório 1º Ofício'),
                    new OA\Property(property: 'cnpj', type: 'string', example: '12.345.678/0001-90'),
                    new OA\Property(property: 'telefone', type: 'string', example: '(11) 3333-4444'),
                    new OA\Property(property: 'email', type: 'string', example: 'contato@cartorio.com.br'),
                    new OA\Property(property: 'logradouro', type: 'string', example: 'Rua das Flores'),
                    new OA\Property(property: 'numero', type: 'integer', example: 100),
                    new OA\Property(property: 'bairro', type: 'string', example: 'Centro'),
                    new OA\Property(property: 'cidade', type: 'string', example: 'São Paulo'),
                    new OA\Property(property: 'estado', type: 'string', example: 'SP'),
                    new OA\Property(property: 'cep', type: 'string', example: '01310-100'),
                    new OA\Property(property: 'responsavel_nome', type: 'string', example: 'João Silva'),
                    new OA\Property(property: 'responsavel_cpf', type: 'string', example: '123.456.789-00'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cartório criado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function store(StoreCartorioRequest $request): JsonResponse
    {
        $cartorio = Cartorio::create($request->validated());

        return response()->json($cartorio, 201);
    }

    #[OA\Get(
        path: '/api/cartorios/{id}',
        tags: ['Cartórios'],
        summary: 'Exibir um cartório',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dados do cartório'),
            new OA\Response(response: 404, description: 'Cartório não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $cartorio = Cartorio::with(['usuarios', 'imoveis'])->findOrFail($id);

        return response()->json($cartorio);
    }

    #[OA\Put(
        path: '/api/cartorios/{id}',
        tags: ['Cartórios'],
        summary: 'Atualizar um cartório',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'nome', type: 'string'),
                new OA\Property(property: 'telefone', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'cidade', type: 'string'),
                new OA\Property(property: 'estado', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cartório atualizado'),
            new OA\Response(response: 404, description: 'Cartório não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function update(UpdateCartorioRequest $request, int $id): JsonResponse
    {
        $cartorio = Cartorio::findOrFail($id);
        $cartorio->update($request->validated());

        return response()->json($cartorio);
    }

    #[OA\Delete(
        path: '/api/cartorios/{id}',
        tags: ['Cartórios'],
        summary: 'Remover um cartório (soft delete)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cartório removido'),
            new OA\Response(response: 404, description: 'Cartório não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $cartorio = Cartorio::findOrFail($id);
        $cartorio->delete();

        return response()->json(['message' => 'Cartório removido com sucesso.']);
    }
}
