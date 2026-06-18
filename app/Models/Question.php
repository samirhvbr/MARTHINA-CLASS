<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const DIFFICULTY_EASY = 'easy';
    public const DIFFICULTY_NORMAL = 'normal';
    public const DIFFICULTY_HARD = 'hard';

    public const WRONG_OPTIONS_BY_DIFFICULTY = [
        self::DIFFICULTY_EASY => 3,
        self::DIFFICULTY_NORMAL => 4,
        self::DIFFICULTY_HARD => 5,
    ];

    protected $fillable = [
        'category_id',
        'prompt',
        'support_text',
        'explanation',
        'type',
        'difficulty',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function correctOption()
    {
        return $this->hasOne(QuestionOption::class)->where('is_correct', true);
    }

    public static function difficultyLabels(): array
    {
        return [
            self::DIFFICULTY_EASY => 'Facil',
            self::DIFFICULTY_NORMAL => 'Normal',
            self::DIFFICULTY_HARD => 'Dificil',
        ];
    }

    public function wrongOptionsToDisplay(): int
    {
        return self::WRONG_OPTIONS_BY_DIFFICULTY[$this->difficulty] ?? self::WRONG_OPTIONS_BY_DIFFICULTY[self::DIFFICULTY_EASY];
    }
}
