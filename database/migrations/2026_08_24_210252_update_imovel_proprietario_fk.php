<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dropa a FK antiga somente se ainda existir (pode ter sido removida em tentativa anterior)
        if (Schema::hasTable('imovel')) {
            $fks = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'imovel'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME = 'imovel_proprietario_id_foreign'
            ");

            if (! empty($fks)) {
                Schema::table('imovel', function (Blueprint $table) {
                    $table->dropForeign(['proprietario_id']);
                });
            }
        }

        // Anula proprietario_id de registros que apontavam para usuario
        // (IDs que não existem na nova tabela proprietario)
        DB::table('imovel')
            ->whereNotNull('proprietario_id')
            ->whereNotIn('proprietario_id', DB::table('proprietario')->pluck('idproprietario'))
            ->update(['proprietario_id' => null]);

        Schema::table('imovel', function (Blueprint $table) {
            // Cria a nova FK apontando para proprietario
            $table->foreign('proprietario_id')
                ->references('idproprietario')
                ->on('proprietario')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('imovel', function (Blueprint $table) {
            $table->dropForeign(['proprietario_id']);

            $table->foreign('proprietario_id')
                ->references('idusuario')
                ->on('usuario')
                ->nullOnDelete();
        });
    }
};
