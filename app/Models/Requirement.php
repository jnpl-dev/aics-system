<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $table = 'requirement';

    protected $primaryKey = 'requirement_id';

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'is_mandatory',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
