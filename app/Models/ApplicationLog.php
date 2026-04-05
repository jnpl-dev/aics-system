<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    protected $table = 'application_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'performed_by',
        'action',
        'from_status',
        'to_status',
        'remarks',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }
}
