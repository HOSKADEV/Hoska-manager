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
            $table->integer('delivery_quality')->default(0)->after('delivered_at')->comment('جودة التسليم (0-100)');
            $table->integer('response_speed')->default(0)->after('delivery_quality')->comment('سرعة الاستجابة (0-100)');
            $table->integer('support_level')->default(0)->after('response_speed')->comment('مستوى الدعم (0-100)');
            $table->integer('expectations_met')->default(0)->after('support_level')->comment('تحقيق التوقعات (0-100)');
            $table->integer('continuation_intent')->default(0)->after('expectations_met')->comment('نية الاستمرار (0-100)');
            $table->integer('final_satisfaction_score')->nullable()->after('continuation_intent')->comment('رضاء العملاء النهائي (0-100)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_quality',
                'response_speed',
                'support_level',
                'expectations_met',
                'continuation_intent',
                'final_satisfaction_score'
            ]);
        });
    }
};
