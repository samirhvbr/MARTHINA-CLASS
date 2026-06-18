<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->foreignId('question_id')->nullable()->after('word_id')->constrained()->nullOnDelete();
            $table->foreignId('selected_option_id')->nullable()->after('question_id')->constrained('question_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropForeign(['selected_option_id']);
            $table->dropColumn(['question_id', 'selected_option_id']);
        });
    }
};
