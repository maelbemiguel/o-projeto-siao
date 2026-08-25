<?php

use App\Models\Imovel;
use App\Models\Proprietario;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function authenticateForProprietarioController(): User
{
    $user = User::query()->create([
        'nome' => 'Usuário de Teste',
        'cpf' => '999.999.999-99',
        'email' => 'proprietario-controller@example.com',
        'password' => 'password',
    ]);

    Sanctum::actingAs($user);

    return $user;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createProprietarioForController(array $attributes = []): Proprietario
{
    static $sequence = 0;

    $sequence++;

    return Proprietario::query()->create(array_merge([
        'nome' => "Proprietário {$sequence}",
        'cpf' => str_pad((string) $sequence, 11, '0', STR_PAD_LEFT),
        'email' => "proprietario{$sequence}@example.com",
        'telefone' => '11999999999',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
    ], $attributes));
}

it('requires authentication for every proprietario endpoint', function (string $method, string $uri, array $payload) {
    $this->json($method, $uri, $payload)->assertUnauthorized();
})->with([
    'index' => ['GET', '/api/proprietarios', []],
    'quick search' => ['GET', '/api/proprietarios/busca?q=Maria', []],
    'store' => ['POST', '/api/proprietarios', ['nome' => 'Maria', 'cpf' => '12345678901']],
    'show' => ['GET', '/api/proprietarios/1', []],
    'update' => ['PUT', '/api/proprietarios/1', ['nome' => 'Maria Atualizada']],
    'destroy' => ['DELETE', '/api/proprietarios/1', []],
]);

it('lists proprietarios alphabetically with pagination metadata', function () {
    authenticateForProprietarioController();

    createProprietarioForController(['nome' => 'Carlos Lima']);
    createProprietarioForController(['nome' => 'Ana Souza']);
    createProprietarioForController(['nome' => 'Bruno Alves']);

    $this->getJson('/api/proprietarios?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.nome', 'Ana Souza')
        ->assertJsonPath('data.1.nome', 'Bruno Alves')
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('per_page', 2)
        ->assertJsonPath('total', 3);
});

it('filters the proprietario listing by search, city, and state', function () {
    authenticateForProprietarioController();

    $matchingProprietario = createProprietarioForController([
        'nome' => 'Ana Silva',
        'cidade' => 'Campinas',
        'estado' => 'SP',
    ]);
    createProprietarioForController([
        'nome' => 'Bruno Souza',
        'cidade' => 'Campinas',
        'estado' => 'RJ',
    ]);
    createProprietarioForController([
        'nome' => 'Carla Silva',
        'cidade' => 'Santos',
        'estado' => 'SP',
    ]);

    $this->getJson('/api/proprietarios?search=Silva&cidade=Campinas&estado=SP')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.idproprietario', $matchingProprietario->idproprietario);
});

it('performs a limited quick search and returns only autocomplete fields', function () {
    authenticateForProprietarioController();

    $firstProprietario = createProprietarioForController([
        'nome' => 'Ana Pereira',
        'email' => 'contato.ana@example.com',
    ]);
    createProprietarioForController([
        'nome' => 'Bruno Pereira',
        'email' => 'contato.bruno@example.com',
    ]);

    $this->getJson('/api/proprietarios/busca?q=contato&limit=1')
        ->assertOk()
        ->assertExactJson([[
            'idproprietario' => $firstProprietario->idproprietario,
            'nome' => $firstProprietario->nome,
            'cpf' => $firstProprietario->cpf,
            'email' => $firstProprietario->email,
            'telefone' => $firstProprietario->telefone,
        ]]);

    $this->getJson('/api/proprietarios/busca?q=')
        ->assertOk()
        ->assertExactJson([]);
});

it('creates a proprietario from valid data', function () {
    authenticateForProprietarioController();

    $payload = [
        'nome' => 'Mariana Costa',
        'cpf' => '123.456.789-01',
        'email' => 'mariana@example.com',
        'telefone' => '11987654321',
        'logradouro' => 'Rua das Flores',
        'numero' => 42,
        'bairro' => 'Centro',
        'cidade' => 'Campinas',
        'estado' => 'SP',
        'cep' => '13000-000',
    ];

    $response = $this->postJson('/api/proprietarios', $payload)
        ->assertCreated()
        ->assertJsonPath('nome', $payload['nome'])
        ->assertJsonPath('cpf', $payload['cpf']);

    $proprietario = Proprietario::query()->findOrFail($response->json('idproprietario'));

    $this->assertModelExists($proprietario);
    expect($proprietario->only(array_keys($payload)))->toBe($payload);
});

it('validates required and formatted fields when creating a proprietario', function () {
    authenticateForProprietarioController();

    $this->postJson('/api/proprietarios', [
        'email' => 'email-invalido',
        'estado' => 'SPO',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nome', 'cpf', 'email', 'estado']);
});

it('rejects duplicate cpf and email when creating a proprietario', function () {
    authenticateForProprietarioController();

    $existingProprietario = createProprietarioForController();

    $this->postJson('/api/proprietarios', [
        'nome' => 'Outro Proprietário',
        'cpf' => $existingProprietario->cpf,
        'email' => $existingProprietario->email,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf', 'email']);
});

it('shows a proprietario with its imoveis count', function () {
    authenticateForProprietarioController();

    $proprietario = createProprietarioForController();

    Imovel::query()->create([
        'matricula' => '100001.1.000001-0',
        'proprietario_id' => $proprietario->idproprietario,
        'proprietario_nome' => $proprietario->nome,
        'proprietario_cpf' => $proprietario->cpf,
    ]);
    Imovel::query()->create([
        'matricula' => '100001.1.000002-9',
        'proprietario_id' => $proprietario->idproprietario,
        'proprietario_nome' => $proprietario->nome,
        'proprietario_cpf' => $proprietario->cpf,
    ]);

    $this->getJson("/api/proprietarios/{$proprietario->idproprietario}")
        ->assertOk()
        ->assertJsonPath('idproprietario', $proprietario->idproprietario)
        ->assertJsonPath('imoveis_count', 2);
});

it('updates a proprietario while allowing its current unique values', function () {
    authenticateForProprietarioController();

    $proprietario = createProprietarioForController();

    $this->putJson("/api/proprietarios/{$proprietario->idproprietario}", [
        'nome' => 'Nome Atualizado',
        'cpf' => $proprietario->cpf,
        'email' => $proprietario->email,
        'cidade' => 'Curitiba',
        'estado' => 'PR',
    ])
        ->assertOk()
        ->assertJsonPath('nome', 'Nome Atualizado')
        ->assertJsonPath('cidade', 'Curitiba');

    expect($proprietario->refresh())
        ->nome->toBe('Nome Atualizado')
        ->cidade->toBe('Curitiba')
        ->estado->toBe('PR');
});

it('rejects another proprietario unique values when updating', function () {
    authenticateForProprietarioController();

    $proprietario = createProprietarioForController();
    $otherProprietario = createProprietarioForController();

    $this->putJson("/api/proprietarios/{$proprietario->idproprietario}", [
        'cpf' => $otherProprietario->cpf,
        'email' => $otherProprietario->email,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf', 'email']);
});

it('soft deletes a proprietario', function () {
    authenticateForProprietarioController();

    $proprietario = createProprietarioForController();

    $this->deleteJson("/api/proprietarios/{$proprietario->idproprietario}")
        ->assertOk()
        ->assertJsonPath('message', 'Proprietário removido com sucesso.');

    $this->assertSoftDeleted($proprietario);
});

it('returns not found for missing proprietarios', function (string $method, array $payload) {
    authenticateForProprietarioController();

    $this->json($method, '/api/proprietarios/999999', $payload)->assertNotFound();
})->with([
    'show' => ['GET', []],
    'update' => ['PUT', ['nome' => 'Nome Atualizado']],
    'destroy' => ['DELETE', []],
]);
