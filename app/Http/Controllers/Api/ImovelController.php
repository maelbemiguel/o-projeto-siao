<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Imovel\StoreImovelRequest;
use App\Http\Requests\Imovel\UpdateImovelRequest;
use App\Models\Imovel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ImovelController extends Controller
{
    #[OA\Get(
        path: '/api/imoveis',
        tags: ['Imóveis'],
        summary: 'Listar todos os imóveis',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Busca por matrícula, logradouro ou cidade'),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filtrar por status'),
            new OA\Parameter(name: 'cartorio_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Filtrar por cartório'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de imóveis'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Imovel::with('cartorio', 'proprietario');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%")
                    ->orWhere('logradouro', 'like', "%{$search}%")
                    ->orWhere('cidade', 'like', "%{$search}%")
                    ->orWhere('proprietario_nome', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($cartorioId = $request->query('cartorio_id')) {
            $query->where('cartorio_id', $cartorioId);
        }

        if ($proprietarioId = $request->query('proprietario_id')) {
            $query->where('proprietario_id', $proprietarioId);
        }

        $imoveis = $query->orderBy('matricula')->paginate($request->query('per_page', 15));

        return response()->json($imoveis);
    }

    #[OA\Post(
        path: '/api/imoveis',
        tags: ['Imóveis'],
        summary: 'Criar um imóvel',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['matricula'],
                properties: [
                    new OA\Property(property: 'matricula', type: 'string', example: 'MAT-001'),
                    new OA\Property(property: 'tipo', type: 'string', example: 'Residencial'),
                    new OA\Property(property: 'logradouro', type: 'string', example: 'Av. Paulista'),
                    new OA\Property(property: 'numero', type: 'integer', example: 1000),
                    new OA\Property(property: 'bairro', type: 'string', example: 'Bela Vista'),
                    new OA\Property(property: 'cidade', type: 'string', example: 'São Paulo'),
                    new OA\Property(property: 'estado', type: 'string', example: 'SP'),
                    new OA\Property(property: 'cep', type: 'string', example: '01310-100'),
                    new OA\Property(property: 'area_total', type: 'number', example: 120.50),
                    new OA\Property(property: 'valor_avaliado', type: 'number', example: 850000.00),
                    new OA\Property(property: 'status', type: 'string', example: 'ativo'),
                    new OA\Property(property: 'proprietario_nome', type: 'string', example: 'Maria Souza'),
                    new OA\Property(property: 'proprietario_cpf', type: 'string', example: '987.654.321-00'),
                    new OA\Property(property: 'cartorio_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Imóvel criado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function store(StoreImovelRequest $request): JsonResponse
    {
        $imovel = Imovel::create($request->validated());

        return response()->json($imovel->load('cartorio'), 201);
    }

    #[OA\Get(
        path: '/api/imoveis/{id}',
        tags: ['Imóveis'],
        summary: 'Exibir um imóvel',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dados do imóvel'),
            new OA\Response(response: 404, description: 'Imóvel não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $imovel = Imovel::with('cartorio', 'proprietario')->findOrFail($id);

        return response()->json($imovel);
    }

    #[OA\Put(
        path: '/api/imoveis/{id}',
        tags: ['Imóveis'],
        summary: 'Atualizar um imóvel',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'tipo', type: 'string'),
                new OA\Property(property: 'status', type: 'string'),
                new OA\Property(property: 'valor_avaliado', type: 'number'),
                new OA\Property(property: 'cartorio_id', type: 'integer'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Imóvel atualizado'),
            new OA\Response(response: 404, description: 'Imóvel não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function update(UpdateImovelRequest $request, int $id): JsonResponse
    {
        $imovel = Imovel::findOrFail($id);
        $imovel->update($request->validated());

        return response()->json($imovel->load('cartorio'));
    }

    #[OA\Delete(
        path: '/api/imoveis/{id}',
        tags: ['Imóveis'],
        summary: 'Remover um imóvel (soft delete)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Imóvel removido'),
            new OA\Response(response: 404, description: 'Imóvel não encontrado'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $imovel = Imovel::findOrFail($id);
        $imovel->delete();

        return response()->json(['message' => 'Imóvel removido com sucesso.']);
    }
}
