// database/migrations/xxxx_xx_xx_xxxxxx_create_reports_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('periode'); // daily, weekly, monthly, custom
            $table->integer('jours')->nullable(); // pour période custom
            $table->date('start_date');
            $table->date('end_date');
            $table->json('stats'); // Stocke les statistiques en JSON
            $table->json('by_fuel')->nullable();
            $table->json('depenses_par_type')->nullable();
            $table->json('ecarts_journaliers')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['start_date', 'end_date']);
            $table->index('periode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}