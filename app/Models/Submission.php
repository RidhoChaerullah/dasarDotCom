<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'user_id',
        'submission_category_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
        'deadline_at_upload',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'immutable_datetime',
            'deadline_at_upload' => 'immutable_datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return $this->status === 'late'
            ? 'Terlambat'
            : 'Sudah mengumpulkan';
    }
}