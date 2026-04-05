<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationReview extends Model
{
    protected $table = 'application_review';

    protected $primaryKey = 'review_id';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'reviewed_by',
        'stage',
        'decision',
        'feedback_remarks',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
}
