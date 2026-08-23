<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('review_padre_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('reviews')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['review_padre_id']);
            $table->dropColumn('review_padre_id');
        });
    }
};
