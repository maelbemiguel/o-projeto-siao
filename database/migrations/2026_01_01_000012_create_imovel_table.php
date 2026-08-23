<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('imovel')) {
            return;
        }

        Schema::create('imovel', function (Blueprint $table) {
            $table->id('idimovel');
            $table->string('matricula', 50)->unique();
            $table->string('tipo', 80)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->integer('numero')->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->decimal('area_total', 12, 2)->nullable();
            $table->decimal('valor_avaliado', 15, 2)->nullable();
            $table->string('status', 50)->default('ativo');
            $table->unsignedBigInteger('proprietario_id')->nullable();
            $table->string('proprietario_nome', 150)->nullable();
            $table->string('proprietario_cpf', 14)->nullable();
            $table->unsignedBigInteger('cartorio_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cartorio_id')
                ->references('idcartorio')
                ->on('cartorio')
                ->nullOnDelete();

            $table->foreign('proprietario_id')
                ->references('idusuario')
                ->on('usuario')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imovel');
    }
};
