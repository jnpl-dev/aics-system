<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $table = 'application';

    protected $primaryKey = 'application_id';

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'submitted_by',
        'reference_code',
        'status',
        'applicant_last_name',
        'applicant_first_name',
        'applicant_middle_name',
        'applicant_sex',
        'applicant_dob',
        'applicant_address',
        'applicant_phone',
        'applicant_relationship_to_beneficiary',
        'beneficiary_last_name',
        'beneficiary_first_name',
        'beneficiary_middle_name',
        'beneficiary_sex',
        'beneficiary_dob',
        'beneficiary_address',
        'submitted_at',
        'updated_at',
        'reviewed_by',
        'reviewed_at',
        'resubmission_remarks',
        'resubmission_document_ids',
    ];

    protected function casts(): array
    {
        return [
            'applicant_dob' => 'date',
            'beneficiary_dob' => 'date',
            'submitted_at' => 'datetime',
            'updated_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'resubmission_document_ids' => 'array',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'application_id', 'application_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssistanceCategory::class, 'category_id', 'category_id');
    }
}
