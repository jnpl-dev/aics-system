<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected function casts(): array
    {
        return [
            'applicant_dob' => 'date',
            'beneficiary_dob' => 'date',
            'submitted_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
