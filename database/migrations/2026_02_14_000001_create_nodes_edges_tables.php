<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50)->index();           // threat-actor, malware, campaign, observable, etc.
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('confidence')->nullable(); // 0-100
            $table->string('severity', 20)->nullable()->index();  // critical, high, medium, low, unknown
            $table->timestamp('first_seen')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->string('source_ref', 255)->nullable()->index(); // STIX id or external ref
            $table->json('raw')->nullable();                        // Full STIX object or extra metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'severity']);
            $table->index(['name']);
        });

        Schema::create('edges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50)->index();            // uses, targets, attributed-to, indicates, etc.
            $table->uuid('from_node_id');
            $table->uuid('to_node_id');
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('stop_time')->nullable();
            $table->text('description')->nullable();
            $table->json('raw')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('from_node_id')->references('id')->on('nodes')->cascadeOnDelete();
            $table->foreign('to_node_id')->references('id')->on('nodes')->cascadeOnDelete();
            $table->index(['from_node_id', 'to_node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edges');
        Schema::dropIfExists('nodes');
    }
};
