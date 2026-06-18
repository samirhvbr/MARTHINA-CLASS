<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
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
            Subject::updateOrCreate(
                ['slug' => $subject['slug']],
                array_merge($subject, ['is_active' => true])
            );
        }
    }
}
