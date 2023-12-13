<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery  extends Model
{
    use HasFactory;

    protected $table = "deliveries";


    protected $appends = [
        'display_data',
        
    ];
    protected $fillable = [
        'updated_at',
    ];

    public $timestamps = false;

    public function status()
    {
        return $this->hasMany(StatusHistory::class);
    }
    public function getDisplayDataAttribute(){
        $data = Carbon::parse($this->created_at);
        $hoje = Carbon::now();
        $horaFormatada = $data->format('H:i');
    
        if ($data->isSameDay($hoje)) {
            return 'HOJE as '.$horaFormatada ;
        }
    
        $ontem = $hoje->copy()->subDay();
        if ($data->isSameDay($ontem)) {
            return 'ONTEM as  '.$horaFormatada;
        }
    
        $diferencaDias = $data->diffInDays($hoje);
        if ($diferencaDias <= 6) {
            return 'Há ' . $diferencaDias . ' dias as '.$horaFormatada;
        }
    
        return $data->format('d/m/Y');
    }

    public function carriers()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id', 'id');
    }
}
