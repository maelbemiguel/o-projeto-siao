<?php

namespace App\Http\Requests\Imovel;

use Illuminate\Foundation\Http\FormRequest;

class StoreImovelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricula' => ['required', 'string', 'max:50', 'unique:imovel,matricula'],
            'tipo' => ['nullable', 'string', 'max:80'],
            'logradouro' => ['nullable', 'string', 'max:200'],
            'numero' => ['nullable', 'integer'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'area_total' => ['nullable', 'numeric', 'min:0'],
            'valor_avaliado' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'proprietario_id' => ['nullable', 'integer', 'exists:proprietario,idproprietario'],
            'proprietario_nome' => ['nullable', 'string', 'max:150'],
            'proprietario_cpf' => ['nullable', 'string', 'max:14'],
            'cartorio_id' => ['nullable', 'integer', 'exists:cartorio,idcartorio'],
        ];
    }

    public function messages(): array
    {
        return [
            'matricula.required' => 'A matrícula é obrigatória.',
            'matricula.unique' => 'Já existe um imóvel com essa matrícula.',
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
            'proprietario_id.exists' => 'Proprietário não encontrado.',
            'cartorio_id.exists' => 'Cartório não encontrado.',
        ];
    }
}
