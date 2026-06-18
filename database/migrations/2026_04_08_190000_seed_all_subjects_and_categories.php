<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Subjects ───────────────────────────────────────────────
        $subjects = [
            [
                'name' => 'Ingles',
                'slug' => 'eng_',
                'description' => 'Vocabulos e questoes objetivas para desenvolver Ingles.',
                'icon' => 'language',
            ],
            [
                'name' => 'Portugues',
                'slug' => 'prt_',
                'description' => 'Leitura, interpretacao e lingua portuguesa.',
                'icon' => 'book-open-reader',
            ],
            [
                'name' => 'Matematica',
                'slug' => 'mat_',
                'description' => 'Operacoes e raciocinio logico em formato objetivo.',
                'icon' => 'calculator',
            ],
        ];

        foreach ($subjects as $subject) {
            $exists = DB::table('subjects')->where('slug', $subject['slug'])->exists();

            if (!$exists) {
                DB::table('subjects')->insert(array_merge($subject, [
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            } else {
                DB::table('subjects')->where('slug', $subject['slug'])->update([
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── Categories ─────────────────────────────────────────────
        $engId = DB::table('subjects')->where('slug', 'eng_')->value('id');
        $prtId = DB::table('subjects')->where('slug', 'prt_')->value('id');
        $matId = DB::table('subjects')->where('slug', 'mat_')->value('id');

        $categories = [
            // Inglês – vocabulário
            ['subject_id' => $engId, 'name' => 'Animals',     'slug' => 'eng_animals',    'description' => 'Vocabulos em Ingles sobre animais.',         'icon' => 'paw',         'quiz_type' => 'vocabulary'],
            ['subject_id' => $engId, 'name' => 'Food',        'slug' => 'eng_food',       'description' => 'Vocabulos em Ingles sobre alimentos.',       'icon' => 'utensils',    'quiz_type' => 'vocabulary'],
            ['subject_id' => $engId, 'name' => 'Colors',      'slug' => 'eng_colors',     'description' => 'Vocabulos em Ingles sobre cores.',           'icon' => 'palette',     'quiz_type' => 'vocabulary'],
            ['subject_id' => $engId, 'name' => 'Numbers',     'slug' => 'eng_numbers',    'description' => 'Vocabulos em Ingles sobre numeros.',         'icon' => 'hashtag',     'quiz_type' => 'vocabulary'],
            ['subject_id' => $engId, 'name' => 'Family',      'slug' => 'eng_family',     'description' => 'Vocabulos em Ingles sobre familia.',         'icon' => 'users',       'quiz_type' => 'vocabulary'],
            ['subject_id' => $engId, 'name' => 'Body Parts',  'slug' => 'eng_body-parts', 'description' => 'Vocabulos em Ingles sobre o corpo humano.',  'icon' => 'person',      'quiz_type' => 'vocabulary'],
            // Inglês – múltipla escolha
            ['subject_id' => $engId, 'name' => 'English Grammar', 'slug' => 'eng_grammar', 'description' => 'Questoes de multipla escolha sobre gramatica basica.', 'icon' => 'spell-check', 'quiz_type' => 'multiple_choice'],
            // Português
            ['subject_id' => $prtId, 'name' => 'Portugues Leitura', 'slug' => 'prt_reading', 'description' => 'Questoes de leitura e interpretacao em Portugues.', 'icon' => 'book-reader', 'quiz_type' => 'multiple_choice'],
            // Matemática
            ['subject_id' => $matId, 'name' => 'Matematica Basica', 'slug' => 'mat_basic', 'description' => 'Questoes objetivas de matematica basica.', 'icon' => 'calculator', 'quiz_type' => 'multiple_choice'],
        ];

        foreach ($categories as $category) {
            if (!$category['subject_id']) {
                continue;
            }

            $exists = DB::table('categories')->where('slug', $category['slug'])->exists();

            if (!$exists) {
                DB::table('categories')->insert(array_merge($category, [
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        // Não remove dados para preservar registros já vinculados
    }
};
