<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'is_admin',
        'is_active',
        'blocked_at',
        'phone',
        'bio',
        'avatar_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'blocked_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([$this->name, $this->last_name])));
    }

    public function displayName(): string
    {
        return $this->fullName() !== '' ? $this->fullName() : $this->email;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function statusLabel(): string
    {
        if ($this->trashed()) {
            return 'Excluido';
        }

        return $this->isActive() ? 'Ativo' : 'Bloqueado';
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function adminActionsReceived()
    {
        return $this->hasMany(AdminUserAction::class, 'target_user_id');
    }

    public function adminActionsPerformed()
    {
        return $this->hasMany(AdminUserAction::class, 'admin_user_id');
    }
}
