<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $englishGrammar = Category::where('slug', 'eng_grammar')->firstOrFail();
        $portugueseReading = Category::where('slug', 'prt_reading')->firstOrFail();
        $mathematicsBasic = Category::where('slug', 'mat_basic')->firstOrFail();

        $this->seedCategory($englishGrammar, [
            [
                'prompt' => 'Choose the correct sentence in the present tense.',
                'options' => [
                    'She go to school every day.',
                    'She goes to school every day.',
                    'She going to school every day.',
                    'She gone to school every day.',
                ],
                'correct' => 1,
                'explanation' => 'Com he, she ou it, o verbo no presente simples normalmente recebe s ou es.',
            ],
            [
                'prompt' => 'What is the opposite of hot?',
                'options' => ['Warm', 'Cold', 'Dry', 'Late'],
                'correct' => 1,
                'explanation' => 'Cold e o antonimo direto de hot.',
            ],
            [
                'prompt' => 'Complete the sentence: They ____ playing in the park now.',
                'options' => ['is', 'am', 'are', 'be'],
                'correct' => 2,
                'explanation' => 'Para they no present continuous, usamos are.',
            ],
            [
                'prompt' => 'Which option is a correct translation for "Eu gosto de estudar"?',
                'options' => [
                    'I like study.',
                    'I liking to study.',
                    'I like to study.',
                    'I likes studying.',
                ],
                'correct' => 2,
                'explanation' => 'I like to study e uma traducao correta e natural.',
            ],
        ]);

        $this->seedCategory($portugueseReading, [
            [
                'prompt' => 'Leia: "Ana levou um guarda-chuva porque o ceu estava escuro." O que motivou Ana?',
                'options' => [
                    'Ela queria brincar no parque.',
                    'Ela achou que poderia chover.',
                    'Ela precisava se proteger do sol forte.',
                    'Ela perdeu o guarda-chuva anterior.',
                ],
                'correct' => 1,
                'explanation' => 'Ceu escuro sugere chuva, justificando o guarda-chuva.',
            ],
            [
                'prompt' => 'Em qual alternativa todas as palavras estao escritas corretamente?',
                'options' => ['Excecao, necessario, beleza', 'Excessao, nessessario, beleza', 'Excecao, nescessario, belezza', 'Excessao, necessario, belesa'],
                'correct' => 0,
                'explanation' => 'A primeira alternativa apresenta a ortografia correta das tres palavras.',
            ],
            [
                'prompt' => 'Qual frase apresenta linguagem figurada?',
                'options' => [
                    'O relogio marcou oito horas.',
                    'A professora abriu o livro.',
                    'Meu coracao pulou de alegria.',
                    'O menino fechou a porta.',
                ],
                'correct' => 2,
                'explanation' => 'Coracao pulou de alegria e uma expressao figurada.',
            ],
            [
                'prompt' => 'Na frase "Os alunos chegaram cedo", o nucleo do sujeito e:',
                'options' => ['chegaram', 'cedo', 'alunos', 'os'],
                'correct' => 2,
                'explanation' => 'O nucleo do sujeito e o substantivo principal: alunos.',
            ],
        ]);

        $this->seedCategory($mathematicsBasic, [
            [
                'prompt' => 'Quanto e 7 + 5?',
                'options' => ['10', '11', '12', '13'],
                'correct' => 2,
                'explanation' => '7 + 5 = 12.',
            ],
            [
                'prompt' => 'Qual numero completa a sequencia 3, 6, 9, 12, __?',
                'options' => ['14', '15', '16', '18'],
                'correct' => 1,
                'explanation' => 'A sequencia cresce de 3 em 3.',
            ],
            [
                'prompt' => 'Se um caderno custa 8 reais, quanto custam 3 cadernos?',
                'options' => ['16', '21', '24', '32'],
                'correct' => 2,
                'explanation' => '3 x 8 = 24.',
            ],
            [
                'prompt' => 'Qual alternativa representa metade de 18?',
                'options' => ['6', '7', '8', '9'],
                'correct' => 3,
                'explanation' => '18 dividido por 2 e 9.',
            ],
        ]);
    }

    private function seedCategory(Category $category, array $questions): void
    {
        foreach ($questions as $questionIndex => $questionData) {
            $question = Question::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'prompt' => $questionData['prompt'],
                ],
                [
                    'support_text' => $questionData['support_text'] ?? null,
                    'explanation' => $questionData['explanation'] ?? null,
                    'type' => Question::TYPE_MULTIPLE_CHOICE,
                    'difficulty' => $questionData['difficulty'] ?? 'easy',
                    'sort_order' => $questionIndex + 1,
                    'is_active' => true,
                ]
            );

            foreach ($questionData['options'] as $optionIndex => $optionText) {
                $question->options()->updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                    ],
                    [
                        'option_key' => chr(65 + $optionIndex),
                        'is_correct' => $optionIndex === $questionData['correct'],
                        'sort_order' => $optionIndex + 1,
                    ]
                );
            }
        }
    }
}
