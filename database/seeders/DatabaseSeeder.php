<?php

namespace Database\Seeders;

use App\Models\Cartorio;
use App\Models\Imovel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Cartórios ──────────────────────────────────────────────────────────
        $cartorio1 = Cartorio::create([
            'nome' => '1º Ofício de Registro de Imóveis',
            'cnpj' => '12.345.678/0001-90',
            'telefone' => '(11) 3333-1001',
            'email' => 'contato@1oficio.com.br',
            'logradouro' => 'Rua das Flores',
            'numero' => 100,
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01310-100',
            'responsavel_nome' => 'Dr. Carlos Mendes',
            'responsavel_cpf' => '111.222.333-44',
        ]);

        $cartorio2 = Cartorio::create([
            'nome' => '2º Ofício de Registro de Imóveis',
            'cnpj' => '98.765.432/0001-10',
            'telefone' => '(21) 2222-5005',
            'email' => 'contato@2oficio.com.br',
            'logradouro' => 'Av. Atlântica',
            'numero' => 500,
            'bairro' => 'Copacabana',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'cep' => '22010-000',
            'responsavel_nome' => 'Dra. Ana Paula Lima',
            'responsavel_cpf' => '555.666.777-88',
        ]);

        // ── Usuários ───────────────────────────────────────────────────────────
        $admin = User::create([
            'nome' => 'Administrador',
            'cpf' => '000.000.000-00',
            'email' => 'admin@siao.com.br',
            'password' => Hash::make('password'),
            'telefone' => '(11) 99999-0000',
            'endereco' => 'Rua das Flores, 100',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01310-100',
            'cartorio_id' => $cartorio1->idcartorio,
        ]);

        $usuario1 = User::create([
            'nome' => 'João Silva',
            'cpf' => '123.456.789-00',
            'email' => 'joao.silva@email.com',
            'password' => Hash::make('password'),
            'telefone' => '(11) 91234-5678',
            'endereco' => 'Av. Paulista, 1000',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01311-000',
            'cartorio_id' => $cartorio1->idcartorio,
        ]);

        $usuario2 = User::create([
            'nome' => 'Maria Souza',
            'cpf' => '987.654.321-00',
            'email' => 'maria.souza@email.com',
            'password' => Hash::make('password'),
            'telefone' => '(21) 98765-4321',
            'endereco' => 'Rua Ipanema, 200',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'cep' => '22411-000',
            'cartorio_id' => $cartorio2->idcartorio,
        ]);

        $usuario3 = User::create([
            'nome' => 'Pedro Oliveira',
            'cpf' => '456.789.123-00',
            'email' => 'pedro.oliveira@email.com',
            'password' => Hash::make('password'),
            'telefone' => '(31) 97654-3210',
            'endereco' => 'Rua dos Pinheiros, 50',
            'cidade' => 'Belo Horizonte',
            'estado' => 'MG',
            'cep' => '30130-000',
            'cartorio_id' => null,
        ]);

        // ── Imóveis ────────────────────────────────────────────────────────────
        Imovel::create([
            'matricula' => 'MAT-SP-0001',
            'tipo' => 'Residencial',
            'logradouro' => 'Av. Paulista',
            'numero' => 1000,
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01310-100',
            'area_total' => 120.50,
            'valor_avaliado' => 850000.00,
            'status' => 'ativo',
            'proprietario_id' => $usuario1->idusuario,
            'proprietario_nome' => $usuario1->nome,
            'proprietario_cpf' => $usuario1->cpf,
            'cartorio_id' => $cartorio1->idcartorio,
        ]);

        Imovel::create([
            'matricula' => 'MAT-SP-0002',
            'tipo' => 'Comercial',
            'logradouro' => 'Rua Augusta',
            'numero' => 250,
            'bairro' => 'Consolação',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01305-000',
            'area_total' => 350.00,
            'valor_avaliado' => 2500000.00,
            'status' => 'ativo',
            'proprietario_id' => $usuario1->idusuario,
            'proprietario_nome' => $usuario1->nome,
            'proprietario_cpf' => $usuario1->cpf,
            'cartorio_id' => $cartorio1->idcartorio,
        ]);

        Imovel::create([
            'matricula' => 'MAT-RJ-0001',
            'tipo' => 'Residencial',
            'logradouro' => 'Rua Ipanema',
            'numero' => 150,
            'bairro' => 'Ipanema',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'cep' => '22420-010',
            'area_total' => 200.00,
            'valor_avaliado' => 1800000.00,
            'status' => 'ativo',
            'proprietario_id' => $usuario2->idusuario,
            'proprietario_nome' => $usuario2->nome,
            'proprietario_cpf' => $usuario2->cpf,
            'cartorio_id' => $cartorio2->idcartorio,
        ]);

        Imovel::create([
            'matricula' => 'MAT-RJ-0002',
            'tipo' => 'Terreno',
            'logradouro' => 'Estrada do Joá',
            'numero' => 800,
            'bairro' => 'São Conrado',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'cep' => '22610-000',
            'area_total' => 1200.00,
            'valor_avaliado' => 4200000.00,
            'status' => 'pendente',
            'proprietario_id' => $usuario2->idusuario,
            'proprietario_nome' => $usuario2->nome,
            'proprietario_cpf' => $usuario2->cpf,
            'cartorio_id' => $cartorio2->idcartorio,
        ]);

        Imovel::create([
            'matricula' => 'MAT-MG-0001',
            'tipo' => 'Rural',
            'logradouro' => 'Estrada Municipal',
            'numero' => 0,
            'bairro' => 'Zona Rural',
            'cidade' => 'Nova Lima',
            'estado' => 'MG',
            'cep' => '34000-000',
            'area_total' => 50000.00,
            'valor_avaliado' => 3500000.00,
            'status' => 'inativo',
            'proprietario_id' => $usuario3->idusuario,
            'proprietario_nome' => $usuario3->nome,
            'proprietario_cpf' => $usuario3->cpf,
            'cartorio_id' => null,
        ]);

        $this->command->info('✓ Seed concluído!');
        $this->command->info('  Usuário admin: admin@siao.com.br / password');
        $this->command->info('  Usuário teste: joao.silva@email.com / password');
    }
}
