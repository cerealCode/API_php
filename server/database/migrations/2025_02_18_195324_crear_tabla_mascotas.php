<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            // Cada mascota pertenece a un usuario, si se botrra el usuario o se actualiza su id, se actualiza en cascada
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')
                ->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            // Lo anterior también se puede hacer como:
            // $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();

            //Datos de la mascota
            // Nombre de la mascota
            $table->string('nombre',50);
            // Descripción de la mascota
            $table->string('descripcion',250);
            // Tipo de la mascota
            $table->enum('tipo', ['Perro', 'Gato', 'Pájaro','Dragón','Conejo','Hamster','Tortuga','Pez','Serpiente']);
            // Pública o privada
            $table->enum('publica', ['Si', 'No'])->default('No');
            // Megustas
            $table->unsignedInteger('megustas')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
