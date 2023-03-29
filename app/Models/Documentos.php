<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Documentos extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'documentos';

    public function tipos(){
        return $this->belongsTo(Tipos::class,'foreign_key','id_tipo','id');
    }
}
