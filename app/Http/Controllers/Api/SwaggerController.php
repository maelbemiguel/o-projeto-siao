<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Sião Cartórios — API',
    version: '1.0.0',
    description: 'API de gestão cartorial: cartórios, imóveis e usuários.',
    contact: new OA\Contact(email: 'suporte@siao.com.br')
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Informe o token retornado pelo endpoint /api/auth/login'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Servidor local'
)]
class SwaggerController extends Controller {}
