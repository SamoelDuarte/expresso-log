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

    public function deliveries()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($statusHistory) {
            if (strtolower($statusHistory->status) === 'entregue') {
               
            }
        });
    }
}
