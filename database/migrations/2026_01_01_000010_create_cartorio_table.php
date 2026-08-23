<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cartorio')) {
            return;
        }

        Schema::create('cartorio', function (Blueprint $table) {
            $table->id('idcartorio');
            $table->string('nome', 150);
            $table->string('cnpj', 18)->unique();
            $table->string('telefone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->integer('numero')->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->string('responsavel_nome', 150)->nullable();
            $table->string('responsavel_cpf', 14)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartorio');
    }
};
