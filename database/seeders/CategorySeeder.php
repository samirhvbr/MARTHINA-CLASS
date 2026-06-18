<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subject;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $english = Subject::where('slug', 'eng_')->firstOrFail();
        $portuguese = Subject::where('slug', 'prt_')->firstOrFail();
        $mathematics = Subject::where('slug', 'mat_')->firstOrFail();

        $categories = [
            [
                'subject_id' => $english->id,
                'name' => 'Animals',
                'slug' => 'eng_animals',
                'description' => 'Vocabulos em Ingles sobre animais.',
                'icon' => 'paw',
                'quiz_type' => Category::QUIZ_TYPE_VOCABULARY,
            ],
            [
                'subject_id' => $english->id,
                'name' => 'Food',
                'slug' => 'eng_food',
                'description' => 'Vocabulos em Ingles sobre alimentos.',
                'icon' => 'utensils',
                'quiz_type' => Category::QUIZ_TYPE_VOCABULARY,
            ],
            [
                'subject_id' => $english->id,
                'name' => 'Colors',
                'slug' => 'eng_colors',
                'description' => 'Vocabulos em Ingles sobre cores.',
                'icon' => 'palette',
                'quiz_type' => Category::QUIZ_TYPE_VOCABULARY,
            ],
            [
                'subject_id' => $english->id,
                'name' => 'Numbers',
                'slug' => 'eng_numbers',
                'description' => 'Vocabulos em Ingles sobre numeros.',
                'icon' => 'hashtag',
                'quiz_type' => Category::QUIZ_TYPE_VOCABULARY,
            ],
            [
                'subject_id' => $english->id,
                'name' => 'Family',
                'slug' => 'eng_family',
                'description' => 'Vocabulos em Ingles sobre familia.',
                'icon' => 'users',
                'quiz_type' => Category::QUIZ_TYPE_VOCABULARY,
            ],
            [
                'subject_id' => $english->id,
                'name' => 'Body Parts',
                'slug' => 'eng_body-parts',
                'description' => 'Vocabulos em Ingles sobre o corpo humano.',
                'icon' => 'person',
                'quiz_type' => Category::QUIZ_TYPE_VOCABULARY,
            ],
            [
                'subject_id' => $english->id,
                'name' => 'English Grammar',
                'slug' => 'eng_grammar',
                'description' => 'Questoes de multipla escolha sobre gramatica basica.',
                'icon' => 'spell-check',
                'quiz_type' => Category::QUIZ_TYPE_MULTIPLE_CHOICE,
            ],
            [
                'subject_id' => $portuguese->id,
                'name' => 'Portugues Leitura',
                'slug' => 'prt_reading',
                'description' => 'Questoes de leitura e interpretacao em Portugues.',
                'icon' => 'book-reader',
                'quiz_type' => Category::QUIZ_TYPE_MULTIPLE_CHOICE,
            ],
            [
                'subject_id' => $mathematics->id,
                'name' => 'Matematica Basica',
                'slug' => 'mat_basic',
                'description' => 'Questoes objetivas de matematica basica.',
                'icon' => 'calculator',
                'quiz_type' => Category::QUIZ_TYPE_MULTIPLE_CHOICE,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['is_active' => true])
            );
        }
    }
}
