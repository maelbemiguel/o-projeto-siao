<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proprietario extends Model
{
    use SoftDeletes;

    protected $table = 'proprietario';

    protected $primaryKey = 'idproprietario';

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'telefone',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function imoveis(): HasMany
    {
        return $this->hasMany(Imovel::class, 'proprietario_id', 'idproprietario');
    }
}
