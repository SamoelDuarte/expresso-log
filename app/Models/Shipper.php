<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipper extends Model
{
    use HasFactory;

    protected $table = "shippers";

    protected $fillable = [
        'code',
        'legal_name',
        'trade_name',
        'cpf_cnpj',
        'rg_state_registration',
        'zip_code',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'complement'
    ];
}
