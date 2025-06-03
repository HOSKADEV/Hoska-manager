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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->decimal('amount',10,2)->default(0);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->foreignId('project_id')->constrained()->cascadeOnDelete(); // علاقة مع clients
            $table->foreignId('client_id')->constrained()->cascadeOnDelete(); // علاقة مع clients
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
