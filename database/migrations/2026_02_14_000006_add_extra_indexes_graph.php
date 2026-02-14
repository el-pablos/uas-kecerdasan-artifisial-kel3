<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->index('first_seen');
            $table->index('last_seen');
            $table->index('confidence');
        });

        // edges.type already indexed in original migration
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropIndex(['first_seen']);
            $table->dropIndex(['last_seen']);
            $table->dropIndex(['confidence']);
        });
    }
};
