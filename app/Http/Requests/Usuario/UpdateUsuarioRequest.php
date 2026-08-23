<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('usuario');

        return [
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'cpf' => ['sometimes', 'required', 'string', 'max:14', "unique:usuario,cpf,{$id},idusuario"],
            'email' => ['sometimes', 'required', 'email', 'max:150', "unique:usuario,email,{$id},idusuario"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:200'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'cartorio_id' => ['nullable', 'integer', 'exists:cartorio,idcartorio'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => 'Já existe um usuário com esse CPF.',
            'email.unique' => 'Já existe um usuário com esse e-mail.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
            'cartorio_id.exists' => 'Cartório não encontrado.',
        ];
    }
}
