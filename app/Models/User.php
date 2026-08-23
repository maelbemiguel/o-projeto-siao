<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuario';

    protected $primaryKey = 'idusuario';

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'password',
        'telefone',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'cartorio_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────────

    public function cartorio(): BelongsTo
    {
        return $this->belongsTo(Cartorio::class, 'cartorio_id', 'idcartorio');
    }

    public function imoveis(): HasMany
    {
        return $this->hasMany(Imovel::class, 'proprietario_id', 'idusuario');
    }
}
