<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->after('name')->index();
            $table->text('description')->nullable()->after('slug');
            $table->string('icon')->nullable()->after('description');
            $table->string('quiz_type')->default('vocabulary')->after('icon');
            $table->boolean('is_active')->default(true)->after('quiz_type');
        });

        $timestamp = now();

        DB::table('subjects')->updateOrInsert(
            ['slug' => 'eng_'],
            [
                'name' => 'Ingles',
                'description' => 'Conteudos de vocabulario e questoes objetivas de Ingles.',
                'icon' => 'language',
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        $englishSubjectId = DB::table('subjects')->where('slug', 'eng_')->value('id');

        $legacyVocabularyCategories = [
            'Animals' => [
                'slug' => 'eng_animals',
                'description' => 'Vocabulos em Ingles sobre animais.',
                'icon' => 'paw',
            ],
            'Food' => [
                'slug' => 'eng_food',
                'description' => 'Vocabulos em Ingles sobre alimentos.',
                'icon' => 'utensils',
            ],
            'Colors' => [
                'slug' => 'eng_colors',
                'description' => 'Vocabulos em Ingles sobre cores.',
                'icon' => 'palette',
            ],
            'Numbers' => [
                'slug' => 'eng_numbers',
                'description' => 'Vocabulos em Ingles sobre numeros.',
                'icon' => 'hashtag',
            ],
            'Family' => [
                'slug' => 'eng_family',
                'description' => 'Vocabulos em Ingles sobre familia.',
                'icon' => 'users',
            ],
            'Body Parts' => [
                'slug' => 'eng_body-parts',
                'description' => 'Vocabulos em Ingles sobre o corpo humano.',
                'icon' => 'person',
            ],
        ];

        DB::table('categories')->orderBy('id')->get()->each(function ($category) use ($englishSubjectId, $legacyVocabularyCategories) {
            $legacyConfig = $legacyVocabularyCategories[$category->name] ?? null;

            DB::table('categories')
                ->where('id', $category->id)
                ->update([
                    'subject_id' => $englishSubjectId,
                    'slug' => $legacyConfig['slug'] ?? (Str::slug($category->name) . '-' . $category->id),
                    'description' => $legacyConfig['description'] ?? $category->description,
                    'icon' => $legacyConfig['icon'] ?? $category->icon,
                    'quiz_type' => 'vocabulary',
                    'is_active' => true,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['subject_id', 'slug', 'description', 'icon', 'quiz_type', 'is_active']);
        });
    }
};
