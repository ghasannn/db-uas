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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->enum('status', ['draft', 'published', 'ended'])->default('published');
            $table->boolean('is_free')->default(false);
            $table->integer('quota')->default(0);
            $table->integer('reserved_count')->default(0);
            $table->integer('sold_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'status', 'is_free', 'quota', 'reserved_count', 'sold_count']);
        });
    }
};
