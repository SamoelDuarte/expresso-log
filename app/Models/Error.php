<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    protected $table = 'errors';

    protected $fillable = ['erro']; // Coluna que armazenará os erros
    protected $appends = ['formatted_date'];

    // Método de acesso para a data formatada
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }
}
