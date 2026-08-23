<?php

namespace App\Http\Requests\Cartorio;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('cartorio');

        return [
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'cnpj' => ['sometimes', 'required', 'string', 'max:18', "unique:cartorio,cnpj,{$id},idcartorio"],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'logradouro' => ['nullable', 'string', 'max:200'],
            'numero' => ['nullable', 'integer'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'responsavel_id' => ['nullable', 'integer'],
            'responsavel_nome' => ['nullable', 'string', 'max:150'],
            'responsavel_cpf' => ['nullable', 'string', 'max:14'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnpj.unique' => 'Já existe um cartório com esse CNPJ.',
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
        ];
    }
}
