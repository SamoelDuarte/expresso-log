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
        // Verificar se já existe um registro com o mesmo status e delivery_id
        $existingRecord = static::where('status', $statusHistory->status)
                                ->where('delivery_id', $statusHistory->delivery_id)
                                ->exists();

        // Se já existir um registro, não criar um novo
        if ($existingRecord) {
            return false; // Retorna false para cancelar a criação do registro
        }

        // Verificar se o status é 'entregue' e definir a coluna 'send' como 1
        if (strtolower($statusHistory->status) === 'entregue' 
        || strtolower($statusHistory->status) === 'finalizado'
        || strtolower($statusHistory->status) === 'entrega realizada (mobile)'
        || strtolower($statusHistory->status) === 'entrega realizada'
        || strtolower($statusHistory->status) === 'solicitação em rota'
        || strtolower($statusHistory->status) === ' saiu para entregar') {
            $statusHistory->send = 1;
        }
    });
}
}
