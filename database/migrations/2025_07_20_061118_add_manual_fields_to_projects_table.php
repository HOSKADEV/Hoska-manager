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
        Schema::table('projects', function (Blueprint $table) {
            // للسماح بإدخال المشاريع القديمة يدوياً
            // $table->unsignedInteger('manual_hours_spent')->nullable();
            // $table->decimal('manual_cost', 15, 2)->nullable();
            // $table->boolean('is_manual')->default(false)->after('manual_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // $table->dropColumn(['manual_hours_spent', 'manual_cost', 'is_manual']);
        });
    }
};
