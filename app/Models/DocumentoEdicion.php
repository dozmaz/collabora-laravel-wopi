<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DocumentoEdicion extends Model
{
    use HasFactory;
    public static $enProceso = 'EN PROCESO';
    protected $table = 'documentos_edicion';

    /**
     * valida si todavía se puede editar en la fecha establecida
     *
     * @return boolean
     */
    public function puedeEditarPorFecha():bool
    {
        $past = new \DateTime($this->fecha_limite_edicion);
        $now = new \DateTime("now");
        if ($now <= $past) {
            if($this->estado = self::$enProceso){
                return true;
            }
        } else {
            $this->estado = "FINALIZADO";
            $this->save();
        }
        return false;
    }
}
