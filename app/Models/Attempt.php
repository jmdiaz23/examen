<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'uuid',
        'full_name',
        'semester',
        'email',
        'started_at',
        'expires_at',
        'submitted_at',
        'score',
        'total_questions',
        'status',
        'question_ids',
        'ip_address',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        'total_questions' => 'integer',
        'question_ids' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attempt) {
            if (empty($attempt->uuid)) {
                $attempt->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['submitted', 'expired'], true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function remainingSeconds(): int
    {
        if ($this->expires_at === null) {
            return 0;
        }

        return max(0, $this->expires_at->getTimestamp() - now()->getTimestamp());
    }

    public function passed(): bool
    {
        return $this->score !== null && $this->score >= $this->exam->passing_score;
    }
}
