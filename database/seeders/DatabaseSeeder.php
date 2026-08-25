<?php

namespace Database\Seeders;

use App\Models\Cartorio;
use App\Models\Imovel;
use App\Models\Proprietario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $cartorios = $this->seedCartorios();
        $usuarios = $this->seedUsuarios($cartorios);

        $this->associateResponsaveis($cartorios, $usuarios);

        $proprietarios = $this->seedProprietarios();

        $this->seedImoveis($cartorios, $proprietarios);

        $this->command?->info('Seed concluído com sucesso.');
        $this->command?->info('Acesso: admin@siao.com.br / '.self::DEFAULT_PASSWORD);
    }

    /**
     * @return array<string, Cartorio>
     */
    private function seedCartorios(): array
    {
        $cartorios = [];

        foreach ([
            [
                'nome' => '1º Oficial de Registro de Imóveis de São Paulo',
                'cnpj' => '12.345.678/0001-95',
                'telefone' => '(11) 3100-1001',
                'email' => 'atendimento.sp@siao.test',
                'logradouro' => 'Rua da Consolação',
                'numero' => 382,
                'bairro' => 'Consolação',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01302-000',
                'responsavel_nome' => 'Mariana Costa',
                'responsavel_cpf' => '123.456.789-09',
            ],
            [
                'nome' => '2º Ofício de Registro de Imóveis do Rio de Janeiro',
                'cnpj' => '98.765.432/0001-98',
                'telefone' => '(21) 3200-2002',
                'email' => 'atendimento.rj@siao.test',
                'logradouro' => 'Avenida Rio Branco',
                'numero' => 156,
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '20040-901',
                'responsavel_nome' => 'Rafael Nunes',
                'responsavel_cpf' => '246.813.579-28',
            ],
            [
                'nome' => '1º Registro de Imóveis de Belo Horizonte',
                'cnpj' => '45.123.456/0001-87',
                'telefone' => '(31) 3300-3003',
                'email' => 'atendimento.mg@siao.test',
                'logradouro' => 'Avenida Afonso Pena',
                'numero' => 732,
                'bairro' => 'Centro',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'cep' => '30130-003',
                'responsavel_nome' => 'Beatriz Almeida',
                'responsavel_cpf' => '456.789.123-64',
            ],
        ] as $attributes) {
            $cartorio = Cartorio::withTrashed()->updateOrCreate(
                ['cnpj' => $attributes['cnpj']],
                $attributes,
            );

            if ($cartorio->trashed()) {
                $cartorio->restore();
            }

            $cartorios[$cartorio->cnpj] = $cartorio;
        }

        return $cartorios;
    }

    /**
     * @param  array<string, Cartorio>  $cartorios
     * @return array<string, User>
     */
    private function seedUsuarios(array $cartorios): array
    {
        $usuarios = [];
        $password = Hash::make(self::DEFAULT_PASSWORD);

        foreach ([
            [
                'nome' => 'Administrador Sião',
                'cpf' => '111.222.333-96',
                'email' => 'admin@siao.com.br',
                'telefone' => '(11) 99990-0001',
                'endereco' => 'Rua da Consolação, 382',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01302-000',
                'cartorio_cnpj' => '12.345.678/0001-95',
            ],
            [
                'nome' => 'Mariana Costa',
                'cpf' => '123.456.789-09',
                'email' => 'mariana.costa@siao.test',
                'telefone' => '(11) 99990-1001',
                'endereco' => 'Rua Augusta, 450',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01304-000',
                'cartorio_cnpj' => '12.345.678/0001-95',
            ],
            [
                'nome' => 'Rafael Nunes',
                'cpf' => '246.813.579-28',
                'email' => 'rafael.nunes@siao.test',
                'telefone' => '(21) 99990-2002',
                'endereco' => 'Rua do Ouvidor, 90',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '20040-030',
                'cartorio_cnpj' => '98.765.432/0001-98',
            ],
            [
                'nome' => 'Beatriz Almeida',
                'cpf' => '456.789.123-64',
                'email' => 'beatriz.almeida@siao.test',
                'telefone' => '(31) 99990-3003',
                'endereco' => 'Rua da Bahia, 1148',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'cep' => '30160-011',
                'cartorio_cnpj' => '45.123.456/0001-87',
            ],
        ] as $attributes) {
            $cartorioCnpj = $attributes['cartorio_cnpj'];
            unset($attributes['cartorio_cnpj']);

            $attributes['password'] = $password;
            $attributes['cartorio_id'] = $cartorios[$cartorioCnpj]->idcartorio;

            $usuario = User::withTrashed()->updateOrCreate(
                ['email' => $attributes['email']],
                $attributes,
            );

            if ($usuario->trashed()) {
                $usuario->restore();
            }

            $usuarios[$usuario->email] = $usuario;
        }

        return $usuarios;
    }

    /**
     * @param  array<string, Cartorio>  $cartorios
     * @param  array<string, User>  $usuarios
     */
    private function associateResponsaveis(array $cartorios, array $usuarios): void
    {
        foreach ([
            '12.345.678/0001-95' => 'mariana.costa@siao.test',
            '98.765.432/0001-98' => 'rafael.nunes@siao.test',
            '45.123.456/0001-87' => 'beatriz.almeida@siao.test',
        ] as $cartorioCnpj => $usuarioEmail) {
            $cartorios[$cartorioCnpj]->update([
                'responsavel_id' => $usuarios[$usuarioEmail]->idusuario,
            ]);
        }
    }

    /**
     * @return array<string, Proprietario>
     */
    private function seedProprietarios(): array
    {
        $proprietarios = [];

        foreach ([
            [
                'nome' => 'Ana Martins',
                'cpf' => '147.258.369-82',
                'email' => 'ana.martins@example.test',
                'telefone' => '(11) 98881-1001',
                'logradouro' => 'Alameda Santos',
                'numero' => 1500,
                'bairro' => 'Jardins',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01418-100',
            ],
            [
                'nome' => 'Carlos Henrique Souza',
                'cpf' => '258.369.147-37',
                'email' => 'carlos.souza@example.test',
                'telefone' => '(11) 98881-1002',
                'logradouro' => 'Rua Harmonia',
                'numero' => 280,
                'bairro' => 'Vila Madalena',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '05435-000',
            ],
            [
                'nome' => 'Fernanda Ribeiro',
                'cpf' => '369.147.258-37',
                'email' => 'fernanda.ribeiro@example.test',
                'telefone' => '(21) 98882-2001',
                'logradouro' => 'Rua Barão da Torre',
                'numero' => 410,
                'bairro' => 'Ipanema',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '22411-002',
            ],
            [
                'nome' => 'Lucas Oliveira',
                'cpf' => '741.852.963-55',
                'email' => 'lucas.oliveira@example.test',
                'telefone' => '(21) 98882-2002',
                'logradouro' => 'Rua Voluntários da Pátria',
                'numero' => 320,
                'bairro' => 'Botafogo',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '22270-000',
            ],
            [
                'nome' => 'Patrícia Gomes',
                'cpf' => '852.963.741-00',
                'email' => 'patricia.gomes@example.test',
                'telefone' => '(31) 98883-3001',
                'logradouro' => 'Rua Pium-í',
                'numero' => 780,
                'bairro' => 'Anchieta',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'cep' => '30310-080',
            ],
            [
                'nome' => 'Sérgio Carvalho',
                'cpf' => '963.741.852-00',
                'email' => 'sergio.carvalho@example.test',
                'telefone' => '(31) 98883-3002',
                'logradouro' => 'Avenida Bandeirantes',
                'numero' => 1200,
                'bairro' => 'Sion',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'cep' => '30315-382',
            ],
        ] as $attributes) {
            $proprietario = Proprietario::withTrashed()->updateOrCreate(
                ['cpf' => $attributes['cpf']],
                $attributes,
            );

            if ($proprietario->trashed()) {
                $proprietario->restore();
            }

            $proprietarios[$proprietario->cpf] = $proprietario;
        }

        return $proprietarios;
    }

    /**
     * @param  array<string, Cartorio>  $cartorios
     * @param  array<string, Proprietario>  $proprietarios
     */
    private function seedImoveis(array $cartorios, array $proprietarios): void
    {
        foreach ([
            [
                'matricula' => '100001.1.000001-0',
                'tipo' => 'Residencial',
                'logradouro' => 'Avenida Paulista',
                'numero' => 1000,
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01310-100',
                'area_total' => 128.50,
                'valor_avaliado' => 980000.00,
                'status' => 'ativo',
                'proprietario_cpf' => '147.258.369-82',
                'cartorio_cnpj' => '12.345.678/0001-95',
            ],
            [
                'matricula' => '100001.1.000002-9',
                'tipo' => 'Comercial',
                'logradouro' => 'Rua Augusta',
                'numero' => 2450,
                'bairro' => 'Cerqueira César',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01412-100',
                'area_total' => 310.00,
                'valor_avaliado' => 2650000.00,
                'status' => 'ativo',
                'proprietario_cpf' => '258.369.147-37',
                'cartorio_cnpj' => '12.345.678/0001-95',
            ],
            [
                'matricula' => '100001.1.000003-7',
                'tipo' => 'Terreno',
                'logradouro' => 'Rua Heitor Penteado',
                'numero' => 1850,
                'bairro' => 'Sumaré',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '05438-300',
                'area_total' => 640.00,
                'valor_avaliado' => 3200000.00,
                'status' => 'pendente',
                'proprietario_cpf' => '147.258.369-82',
                'cartorio_cnpj' => '12.345.678/0001-95',
            ],
            [
                'matricula' => '200001.1.000001-5',
                'tipo' => 'Residencial',
                'logradouro' => 'Avenida Vieira Souto',
                'numero' => 420,
                'bairro' => 'Ipanema',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '22420-006',
                'area_total' => 215.00,
                'valor_avaliado' => 4850000.00,
                'status' => 'ativo',
                'proprietario_cpf' => '369.147.258-37',
                'cartorio_cnpj' => '98.765.432/0001-98',
            ],
            [
                'matricula' => '200001.1.000002-3',
                'tipo' => 'Comercial',
                'logradouro' => 'Avenida Rio Branco',
                'numero' => 120,
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '20040-001',
                'area_total' => 450.75,
                'valor_avaliado' => 3700000.00,
                'status' => 'inativo',
                'proprietario_cpf' => '741.852.963-55',
                'cartorio_cnpj' => '98.765.432/0001-98',
            ],
            [
                'matricula' => '200001.1.000003-1',
                'tipo' => 'Residencial',
                'logradouro' => 'Rua das Laranjeiras',
                'numero' => 310,
                'bairro' => 'Laranjeiras',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'cep' => '22240-004',
                'area_total' => 96.00,
                'valor_avaliado' => 720000.00,
                'status' => 'cancelado',
                'proprietario_cpf' => '369.147.258-37',
                'cartorio_cnpj' => '98.765.432/0001-98',
            ],
            [
                'matricula' => '300001.1.000001-8',
                'tipo' => 'Rural',
                'logradouro' => 'Estrada de Casa Branca',
                'numero' => 0,
                'bairro' => 'Zona Rural',
                'cidade' => 'Brumadinho',
                'estado' => 'MG',
                'cep' => '35460-000',
                'area_total' => 48500.00,
                'valor_avaliado' => 5900000.00,
                'status' => 'ativo',
                'proprietario_cpf' => '852.963.741-00',
                'cartorio_cnpj' => '45.123.456/0001-87',
            ],
            [
                'matricula' => '300001.1.000002-6',
                'tipo' => 'Terreno',
                'logradouro' => 'Alameda Oscar Niemeyer',
                'numero' => 1360,
                'bairro' => 'Vila da Serra',
                'cidade' => 'Nova Lima',
                'estado' => 'MG',
                'cep' => '34006-056',
                'area_total' => 1200.00,
                'valor_avaliado' => 2250000.00,
                'status' => 'pendente',
                'proprietario_cpf' => '963.741.852-00',
                'cartorio_cnpj' => '45.123.456/0001-87',
            ],
            [
                'matricula' => '300001.1.000003-4',
                'tipo' => 'Industrial',
                'logradouro' => 'Avenida das Indústrias',
                'numero' => 2100,
                'bairro' => 'Distrito Industrial',
                'cidade' => 'Contagem',
                'estado' => 'MG',
                'cep' => '32210-000',
                'area_total' => 8750.00,
                'valor_avaliado' => 12600000.00,
                'status' => 'ativo',
                'proprietario_cpf' => '852.963.741-00',
                'cartorio_cnpj' => '45.123.456/0001-87',
            ],
        ] as $attributes) {
            $cartorioCnpj = $attributes['cartorio_cnpj'];
            unset($attributes['cartorio_cnpj']);

            $proprietario = $proprietarios[$attributes['proprietario_cpf']];
            $attributes['proprietario_id'] = $proprietario->idproprietario;
            $attributes['proprietario_nome'] = $proprietario->nome;
            $attributes['cartorio_id'] = $cartorios[$cartorioCnpj]->idcartorio;

            $imovel = Imovel::withTrashed()->updateOrCreate(
                ['matricula' => $attributes['matricula']],
                $attributes,
            );

            if ($imovel->trashed()) {
                $imovel->restore();
            }
        }
    }
}
