<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proprietario')) {
            return;
        }

        Schema::create('proprietario', function (Blueprint $table) {
            $table->id('idproprietario');
            $table->string('nome', 150);
            $table->string('cpf', 14)->unique();
            $table->string('email', 150)->nullable()->unique();
            $table->string('telefone', 20)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->integer('numero')->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proprietario');
    }
};
