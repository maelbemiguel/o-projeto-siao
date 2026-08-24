<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Imovel extends Model
{
    use SoftDeletes;

    protected $table = 'imovel';

    protected $primaryKey = 'idimovel';

    protected $fillable = [
        'matricula',
        'tipo',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'area_total',
        'valor_avaliado',
        'status',
        'proprietario_id',
        'proprietario_nome',
        'proprietario_cpf',
        'cartorio_id',
    ];

    protected function casts(): array
    {
        return [
            'area_total' => 'decimal:2',
            'valor_avaliado' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function cartorio(): BelongsTo
    {
        return $this->belongsTo(Cartorio::class, 'cartorio_id', 'idcartorio');
    }

    public function proprietario(): BelongsTo
    {
        return $this->belongsTo(Proprietario::class, 'proprietario_id', 'idproprietario');
    }
}
