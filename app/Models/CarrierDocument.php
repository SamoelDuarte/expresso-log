<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierDocument extends Model
{
    use HasFactory;
    protected $table = "carrier_documents";
    protected $fillable = [
        'number'
    ];

    public function carrier()
    {
        return $this->belongsTo(Delivery::class);
    } 
}
