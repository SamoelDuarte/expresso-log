<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;
    protected $table = 'carriers';
    
    protected $fillable = [
        'id',
        'legal_name',
        'trade_name',
        'cnpj',
        'state_registration',
        'phone',
        'rntrc',
        'rntrc_expiry_date',
        'opted_for_simples_nacional',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'landmark',
        'contact_name',
        'contact_phone1',
        'contact_phone2',
        'contact_email',
        'customer_service_phone',
        'customer_service_email',
        'website',
        'title',
        'message',
        'created_at',
        'updated_at',
    ];


    public function documents()
    {
        return $this->hasMany(CarrierDocument::class);
    }
}
