<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery  extends Model
{
    use HasFactory;
    protected $table = "deliveries";


    public function status()
    {
        return $this->hasMany(StatusHistory::class);
    }

    public function carriers()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id', 'id');
    }
}
