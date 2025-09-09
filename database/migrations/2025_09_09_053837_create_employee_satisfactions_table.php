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
        Schema::create('employee_satisfactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');

            // التقييمات (من 1 إلى 5 مثلا)
            $table->unsignedTinyInteger('salary_compensation')->comment('الراتب والتعويضات');
            $table->unsignedTinyInteger('work_environment')->comment('بيئة العمل');
            $table->unsignedTinyInteger('colleagues_relationship')->comment('العلاقات مع الزملاء');
            $table->unsignedTinyInteger('management_relationship')->comment('العلاقة مع الإدارة');
            $table->unsignedTinyInteger('growth_opportunities')->comment('فرص النمو والتطور');
            $table->unsignedTinyInteger('work_life_balance')->comment('التوازن بين العمل والحياة');

            $table->timestamps();
            $table->unique(['employee_id', 'month', 'year'], 'unique_employee_month_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_satisfactions');
    }
};
