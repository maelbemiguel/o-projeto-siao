<?php

use App\Models\Cartorio;
use App\Models\Imovel;
use App\Models\Proprietario;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

it('seeds a coherent dataset for the application', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Cartorio::count())->toBe(3)
        ->and(User::count())->toBe(4)
        ->and(Proprietario::count())->toBe(6)
        ->and(Imovel::count())->toBe(9)
        ->and(Imovel::query()->distinct()->orderBy('status')->pluck('status')->all())
        ->toBe(['ativo', 'cancelado', 'inativo', 'pendente']);

    $admin = User::where('email', 'admin@siao.com.br')->firstOrFail();
    $imovel = Imovel::with(['cartorio', 'proprietario'])
        ->where('matricula', '100001.1.000001-0')
        ->firstOrFail();

    expect(Hash::check('password', $admin->password))->toBeTrue()
        ->and($imovel->cartorio)->not->toBeNull()
        ->and($imovel->proprietario)->not->toBeNull()
        ->and($imovel->proprietario_nome)->toBe($imovel->proprietario->nome)
        ->and($imovel->proprietario_cpf)->toBe($imovel->proprietario->cpf);

    Cartorio::all()->each(function (Cartorio $cartorio): void {
        $responsavel = User::findOrFail($cartorio->responsavel_id);

        expect($responsavel->nome)->toBe($cartorio->responsavel_nome)
            ->and($responsavel->cpf)->toBe($cartorio->responsavel_cpf);
    });
});

it('can be run repeatedly and restores its soft deleted records', function () {
    $this->seed(DatabaseSeeder::class);

    Imovel::where('matricula', '200001.1.000003-1')->firstOrFail()->delete();

    $this->seed(DatabaseSeeder::class);

    expect(Cartorio::count())->toBe(3)
        ->and(User::count())->toBe(4)
        ->and(Proprietario::count())->toBe(6)
        ->and(Imovel::count())->toBe(9)
        ->and(Imovel::onlyTrashed()->count())->toBe(0);
});
