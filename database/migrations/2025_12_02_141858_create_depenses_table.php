<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepensesTable extends Migration
{
    public function up()
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_saisie_id')->constrained('shift_saisies')->onDelete('cascade');
            $table->string('type_depense');
            $table->decimal('montant', 12, 2);
            $table->text('description')->nullable();
            $table->string('justificatif')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('depenses');
    }
}