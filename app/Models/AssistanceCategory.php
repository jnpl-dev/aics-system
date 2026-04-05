<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistanceCategory extends Model
{
    protected $table = 'assistance_category';

    protected $primaryKey = 'category_id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
