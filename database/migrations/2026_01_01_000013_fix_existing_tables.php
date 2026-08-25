<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige tabelas preexistentes para alinhá-las aos Models:
 *
 * - Renomeia update_at para updated_at em cartorio;
 * - Permite NULL em cartorio.responsavel_id;
 * - Aumenta usuario.password para 255 caracteres;
 * - Renomeia endereço para endereco em usuario;
 * - Adiciona remember_token em usuario;
 * - Adiciona deleted_at em imovel.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tabela cartorio
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn('cartorio', 'update_at') &&
            ! Schema::hasColumn('cartorio', 'updated_at')
        ) {
            Schema::table('cartorio', function (Blueprint $table) {
                $table->renameColumn('update_at', 'updated_at');
            });
        }

        if (Schema::hasColumn('cartorio', 'responsavel_id')) {
            Schema::table('cartorio', function (Blueprint $table) {
                $table->integer('responsavel_id')
                    ->nullable()
                    ->default(null)
                    ->change();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Tabela usuario
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('usuario', 'password')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('password', 255)->change();
            });
        }

        if (
            Schema::hasColumn('usuario', 'endereço') &&
            ! Schema::hasColumn('usuario', 'endereco')
        ) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->renameColumn('endereço', 'endereco');
            });

            Schema::table('usuario', function (Blueprint $table) {
                $table->string('endereco', 200)
                    ->nullable()
                    ->change();
            });
        }

        if (! Schema::hasColumn('usuario', 'remember_token')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->rememberToken();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Tabela imovel
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('imovel', 'deleted_at')) {
            Schema::table('imovel', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * This migration conditionally repairs unknown legacy schemas, so their
     * original shape cannot be restored without risking existing columns.
     */
    public function down(): void {}
};
