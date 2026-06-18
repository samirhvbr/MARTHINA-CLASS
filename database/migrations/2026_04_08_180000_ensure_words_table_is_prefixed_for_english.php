<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropScoresWordForeignIfPossible();

        if (Schema::hasTable('words') && !Schema::hasTable('eng_words')) {
            Schema::rename('words', 'eng_words');
        }

        $this->addScoresWordForeignToEngWordsIfPossible();
    }

    public function down(): void
    {
        $this->dropScoresWordForeignIfPossible();

        if (Schema::hasTable('eng_words') && !Schema::hasTable('words')) {
            Schema::rename('eng_words', 'words');
        }

        if (Schema::hasTable('scores') && Schema::hasColumn('scores', 'word_id') && Schema::hasTable('words')) {
            try {
                Schema::table('scores', function (Blueprint $table) {
                    $table->foreign('word_id')->references('id')->on('words')->cascadeOnDelete();
                });
            } catch (Throwable $exception) {
                // Ignora se a chave ja existir ou o banco nao permitir recriacao neste estado.
            }
        }
    }

    private function dropScoresWordForeignIfPossible(): void
    {
        if (!Schema::hasTable('scores') || !Schema::hasColumn('scores', 'word_id')) {
            return;
        }

        try {
            Schema::table('scores', function (Blueprint $table) {
                $table->dropForeign(['word_id']);
            });
        } catch (Throwable $exception) {
            // Ignora se a chave nao existir ou ja tiver sido removida antes.
        }
    }

    private function addScoresWordForeignToEngWordsIfPossible(): void
    {
        if (!Schema::hasTable('scores') || !Schema::hasColumn('scores', 'word_id') || !Schema::hasTable('eng_words')) {
            return;
        }

        try {
            Schema::table('scores', function (Blueprint $table) {
                $table->foreign('word_id')->references('id')->on('eng_words')->cascadeOnDelete();
            });
        } catch (Throwable $exception) {
            // Ignora se a chave ja existir ou o banco nao permitir recriacao neste estado.
        }
    }
};
