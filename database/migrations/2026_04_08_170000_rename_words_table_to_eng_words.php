<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('scores') && Schema::hasColumn('scores', 'word_id')) {
            Schema::table('scores', function (Blueprint $table) {
                $table->dropForeign(['word_id']);
            });
        }

        if (Schema::hasTable('words') && !Schema::hasTable('eng_words')) {
            Schema::rename('words', 'eng_words');
        }

        if (Schema::hasTable('scores') && Schema::hasColumn('scores', 'word_id')) {
            Schema::table('scores', function (Blueprint $table) {
                $table->foreign('word_id')->references('id')->on('eng_words')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('scores') && Schema::hasColumn('scores', 'word_id')) {
            Schema::table('scores', function (Blueprint $table) {
                $table->dropForeign(['word_id']);
            });
        }

        if (Schema::hasTable('eng_words') && !Schema::hasTable('words')) {
            Schema::rename('eng_words', 'words');
        }

        if (Schema::hasTable('scores') && Schema::hasColumn('scores', 'word_id')) {
            Schema::table('scores', function (Blueprint $table) {
                $table->foreign('word_id')->references('id')->on('words')->cascadeOnDelete();
            });
        }
    }
};
