<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{

    use HasFactory;
    protected $table = "status_history";
    protected $fillable = [
        'status',
        'observation',
        'detail',
        'delivery_id',
        'external_code'
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class);
    }
}
