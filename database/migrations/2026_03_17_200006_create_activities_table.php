<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['note', 'stage_change', 'call', 'email', 'meeting', 'other']);
            $table->text('description');
            $table->foreignId('old_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('new_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->timestamps();

            $table->index(['deal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
