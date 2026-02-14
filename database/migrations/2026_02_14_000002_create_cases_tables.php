<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('type', 30)->default('incident'); // incident, rfi, takedown
            $table->string('severity', 20)->default('medium'); // critical, high, medium, low
            $table->string('status', 30)->default('open');     // open, in-progress, closed
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
        });

        Schema::create('case_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('case_id');
            $table->string('title', 255);
            $table->string('status', 20)->default('pending'); // pending, in-progress, done
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('cases')->cascadeOnDelete();
        });

        Schema::create('case_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('case_id');
            $table->string('itemable_type');
            $table->uuid('itemable_id');
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('cases')->cascadeOnDelete();
            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_items');
        Schema::dropIfExists('case_tasks');
        Schema::dropIfExists('cases');
    }
};
