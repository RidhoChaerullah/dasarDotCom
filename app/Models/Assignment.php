<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'submission_category_id',
        'created_by',
        'title',
        'course_name',
        'class_name',
        'semester',
        'instructions',
        'due_at',
        'is_open',
        'allow_late',
        'submission_directory',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'immutable_datetime',
            'is_open' => 'boolean',
            'allow_late' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            SubmissionCategory::class,
            'submission_category_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acceptsSubmissions(): bool
    {
        return $this->is_open
            && ($this->allow_late || ! $this->due_at->isPast());
    }

    public function collectionStatus(): string
    {
        if (! $this->is_open) {
            return 'Ditutup pengelola';
        }

        if ($this->due_at->isPast()) {
            return $this->allow_late
                ? 'Menerima pengumpulan terlambat'
                : 'Deadline berakhir';
        }

        return 'Pengumpulan dibuka';
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
