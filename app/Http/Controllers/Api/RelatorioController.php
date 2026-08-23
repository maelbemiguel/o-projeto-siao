<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cartorio;
use App\Models\Imovel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class RelatorioController extends Controller
{
    #[OA\Get(
        path: '/api/relatorios/resumo',
        tags: ['Relatórios'],
        summary: 'Resumo geral do sistema',
        description: 'Retorna totais de cartórios, usuários e imóveis, além de imóveis por status.',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Resumo geral',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'total_cartorios', type: 'integer'),
                    new OA\Property(property: 'total_usuarios', type: 'integer'),
                    new OA\Property(property: 'total_imoveis', type: 'integer'),
                    new OA\Property(property: 'imoveis_por_status', type: 'array', items: new OA\Items(type: 'object')),
                    new OA\Property(property: 'valor_total_avaliado', type: 'number'),
                ])
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function resumo(): JsonResponse
    {
        $imoveisPorStatus = Imovel::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $valorTotal = Imovel::sum('valor_avaliado');

        return response()->json([
            'total_cartorios' => Cartorio::count(),
            'total_usuarios' => User::count(),
            'total_imoveis' => Imovel::count(),
            'imoveis_por_status' => $imoveisPorStatus,
            'valor_total_avaliado' => (float) $valorTotal,
        ]);
    }

    #[OA\Get(
        path: '/api/relatorios/imoveis-por-cartorio',
        tags: ['Relatórios'],
        summary: 'Imóveis agrupados por cartório',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Imóveis por cartório'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function imoveisPorCartorio(): JsonResponse
    {
        $dados = Cartorio::withCount('imoveis')
            ->with(['imoveis' => function ($q) {
                $q->select('idimovel', 'cartorio_id', 'matricula', 'tipo', 'status', 'valor_avaliado');
            }])
            ->orderBy('nome')
            ->get()
            ->map(fn ($c) => [
                'cartorio_id' => $c->idcartorio,
                'cartorio_nome' => $c->nome,
                'total_imoveis' => $c->imoveis_count,
                'valor_total' => $c->imoveis->sum('valor_avaliado'),
                'imoveis' => $c->imoveis,
            ]);

        return response()->json($dados);
    }

    #[OA\Get(
        path: '/api/relatorios/imoveis-por-status',
        tags: ['Relatórios'],
        summary: 'Imóveis agrupados por status',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Imóveis por status'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function imoveisPorStatus(): JsonResponse
    {
        $dados = Imovel::select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(valor_avaliado) as valor_total'))
            ->groupBy('status')
            ->get();

        return response()->json($dados);
    }

    #[OA\Get(
        path: '/api/relatorios/usuarios-por-cartorio',
        tags: ['Relatórios'],
        summary: 'Usuários agrupados por cartório',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Usuários por cartório'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function usuariosPorCartorio(): JsonResponse
    {
        $dados = Cartorio::withCount('usuarios')
            ->orderBy('nome')
            ->get()
            ->map(fn ($c) => [
                'cartorio_id' => $c->idcartorio,
                'cartorio_nome' => $c->nome,
                'total_usuarios' => $c->usuarios_count,
            ]);

        return response()->json($dados);
    }

    #[OA\Get(
        path: '/api/relatorios/imoveis',
        tags: ['Relatórios'],
        summary: 'Relatório detalhado de imóveis com filtros',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'cartorio_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'cidade', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'estado', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'valor_min', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'valor_max', in: 'query', required: false, schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Relatório de imóveis'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function relatorioImoveis(Request $request): JsonResponse
    {
        $query = Imovel::with('cartorio:idcartorio,nome');

        if ($v = $request->query('status')) {
            $query->where('status', $v);
        }
        if ($v = $request->query('cartorio_id')) {
            $query->where('cartorio_id', $v);
        }
        if ($v = $request->query('cidade')) {
            $query->where('cidade', 'like', "%{$v}%");
        }
        if ($v = $request->query('estado')) {
            $query->where('estado', $v);
        }
        if ($v = $request->query('valor_min')) {
            $query->where('valor_avaliado', '>=', $v);
        }
        if ($v = $request->query('valor_max')) {
            $query->where('valor_avaliado', '<=', $v);
        }

        $imoveis = $query->orderBy('matricula')->paginate($request->query('per_page', 50));

        return response()->json($imoveis);
    }
}
