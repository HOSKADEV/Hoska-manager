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
        Schema::table('developments', function (Blueprint $table) {
              $table->enum('currency', ['EUR', 'USD', 'DZD'])->nullable()->after('amount');
            $table->date('start_date')->nullable()->after('currency');
            $table->integer('duration_days')->nullable()->after('start_date');
            $table->date('delivery_date')->nullable()->after('duration_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developments', function (Blueprint $table) {
            $table->dropColumn(['currency', 'start_date', 'duration_days', 'delivery_date']);
        });
    }
};
