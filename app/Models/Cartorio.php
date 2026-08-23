<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cartorio extends Model
{
    use SoftDeletes;

    protected $table = 'cartorio';

    protected $primaryKey = 'idcartorio';

    protected $fillable = [
        'nome',
        'cnpj',
        'telefone',
        'email',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'responsavel_id',
        'responsavel_nome',
        'responsavel_cpf',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'cartorio_id', 'idcartorio');
    }

    public function imoveis(): HasMany
    {
        return $this->hasMany(Imovel::class, 'cartorio_id', 'idcartorio');
    }
}
