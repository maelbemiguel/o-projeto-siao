<?php

namespace App\Http\Requests\Proprietario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProprietarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('proprietario');

        return [
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'cpf' => ['sometimes', 'required', 'string', 'max:14', "unique:proprietario,cpf,{$id},idproprietario"],
            'email' => ['nullable', 'email', 'max:150', "unique:proprietario,email,{$id},idproprietario"],
            'telefone' => ['nullable', 'string', 'max:20'],
            'logradouro' => ['nullable', 'string', 'max:200'],
            'numero' => ['nullable', 'integer'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Já existe um proprietário com esse CPF.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe um proprietário com esse e-mail.',
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
        ];
    }
}
