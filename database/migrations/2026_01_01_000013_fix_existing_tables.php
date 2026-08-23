<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige as tabelas pré-existentes para alinhar com os models Laravel:
 * - Renomeia update_at → updated_at na tabela cartorio
 * - Aumenta password para 255 chars na tabela usuario
 * - Renomeia endereço → endereco na tabela usuario
 * - Adiciona remember_token na tabela usuario
 * - Adiciona deleted_at na tabela imovel
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── cartorio ──────────────────────────────────────────────────────────
        if (Schema::hasColumn('cartorio', 'update_at') && ! Schema::hasColumn('cartorio', 'updated_at')) {
            DB::statement('ALTER TABLE cartorio CHANGE `update_at` `updated_at` TIMESTAMP NULL DEFAULT NULL');
        }

        // Garante que responsavel_id aceita NULL (tabela pré-existente pode não ter DEFAULT NULL)
        DB::statement('ALTER TABLE cartorio MODIFY `responsavel_id` INT NULL DEFAULT NULL');

        // ── usuario ───────────────────────────────────────────────────────────
        // Aumenta o campo password para comportar hashes bcrypt (60 chars)
        DB::statement('ALTER TABLE usuario MODIFY `password` VARCHAR(255) NOT NULL');

        // Renomeia endereço → endereco (remove acento)
        if (Schema::hasColumn('usuario', 'endereço') && ! Schema::hasColumn('usuario', 'endereco')) {
            DB::statement('ALTER TABLE usuario CHANGE `endereço` `endereco` VARCHAR(200) NULL');
        }

        // Adiciona remember_token se não existir
        if (! Schema::hasColumn('usuario', 'remember_token')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->rememberToken()->after('cartorio_id');
            });
        }

        // ── imovel ────────────────────────────────────────────────────────────
        if (! Schema::hasColumn('imovel', 'deleted_at')) {
            Schema::table('imovel', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        // Reversão simplificada
        if (Schema::hasColumn('cartorio', 'updated_at') && ! Schema::hasColumn('cartorio', 'update_at')) {
            DB::statement('ALTER TABLE cartorio CHANGE `updated_at` `update_at` TIMESTAMP NULL DEFAULT NULL');
        }
    }
};
