<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'duration_minutes',
        'questions_to_show',
        'shuffle_questions',
        'shuffle_options',
        'passing_score',
        'show_correct_answers',
        'is_active',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'questions_to_show' => 'integer',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'passing_score' => 'integer',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $exam) {
            if (empty($exam->uuid)) {
                $exam->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function isWithinAvailabilityWindow(): bool
    {
        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }

        return true;
    }

    public function availabilityReason(): ?string
    {
        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return 'Este examen estará disponible a partir del ' . $this->available_from->timezone(config('app.timezone'))->format('d/m/Y H:i');
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return 'Este examen cerró el ' . $this->available_until->timezone(config('app.timezone'))->format('d/m/Y H:i');
        }

        return null;
    }
}
