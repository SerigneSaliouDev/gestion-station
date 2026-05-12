<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY customer_type VARCHAR(50) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY customer_type ENUM('retail','wholesale','corporate') NULL");
    }
};