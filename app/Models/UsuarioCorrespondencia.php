<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class UsuarioCorrespondencia extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'users';
}
