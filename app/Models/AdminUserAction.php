<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUserAction extends Model
{
    use HasFactory;

    public const ACTION_BLOCK = 'block';
    public const ACTION_UNBLOCK = 'unblock';
    public const ACTION_DELETE = 'delete';
    public const ACTION_RESTORE = 'restore';

    protected $fillable = [
        'admin_user_id',
        'target_user_id',
        'action',
        'justification',
    ];

    public function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id')->withTrashed();
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id')->withTrashed();
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_BLOCK => 'Bloqueio manual',
            self::ACTION_UNBLOCK => 'Desbloqueio manual',
            self::ACTION_DELETE => 'Exclusao logica',
            self::ACTION_RESTORE => 'Restauracao',
            default => ucfirst((string) $this->action),
        };
    }
}
