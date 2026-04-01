<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'ip_address',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }
}
