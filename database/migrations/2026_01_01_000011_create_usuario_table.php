<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario')) {
            return;
        }

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('idusuario');
            $table->string('nome', 150);
            $table->string('cpf', 14)->unique();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('telefone', 20)->nullable();
            $table->string('endereco', 200)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->unsignedBigInteger('cartorio_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cartorio_id')
                ->references('idcartorio')
                ->on('cartorio')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
