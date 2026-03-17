<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('vertical_id')->constrained()->restrictOnDelete();
            $table->foreignId('stage_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('currency', ['BOB', 'USD', 'COP'])->default('BOB');
            $table->unsignedTinyInteger('probability')->default(0);
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('lost_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['stage_id', 'assigned_to']);
            $table->index('vertical_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
