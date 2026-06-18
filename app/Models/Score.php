<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    public const XP_PER_CORRECT_ANSWER = 10;

    protected $fillable = [
        'user_id',
        'category_id',
        'word_id',
        'question_id',
        'selected_option_id',
        'score',
        'xp',
        'correct',
        'answer',
        'total_questions'
    ];

    public static function xpForAnswer(bool $correct): int
    {
        return $correct ? self::XP_PER_CORRECT_ANSWER : 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function word()
    {
        return $this->belongsTo(Word::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }
}
