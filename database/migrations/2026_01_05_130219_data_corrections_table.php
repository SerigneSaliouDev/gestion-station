<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_corrections', function (Blueprint $table) {
        $table->id();
        $table->string('correction_type'); // shift, movement, expense, etc.
        $table->foreignId('record_id');
        $table->text('original_values');
        $table->text('corrected_values');
        $table->text('reason');
        $table->foreignId('corrected_by')->constrained('users');
        $table->timestamp('corrected_at');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_corrections');
    }
};
