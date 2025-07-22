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
            $table->foreignId('marketer_id')->nullable()->after('client_id')->constrained('users')->nullOnDelete();
            $table->decimal('marketer_commission_percent', 5, 2)->nullable()->after('marketer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['marketer_id']);
            $table->dropColumn(['marketer_id', 'marketer_commission_percent']);
        });
    }
};
