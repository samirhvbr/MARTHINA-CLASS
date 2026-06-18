<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public const QUIZ_TYPE_VOCABULARY = 'vocabulary';
    public const QUIZ_TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    protected $fillable = [
        'subject_id',
        'name',
        'slug',
        'description',
        'icon',
        'quiz_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function words()
    {
        return $this->hasMany(Word::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function isVocabularyQuiz(): bool
    {
        return $this->quiz_type === self::QUIZ_TYPE_VOCABULARY;
    }

}
