<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo_rue',
        'departamento',
        'provincia',
        'municipio',
        'distrito',
        'area',
        'dependencia',
        'niveles',
        'turnos',
        'director',
        'direccion',
        'telefonos',
        'url_ficha',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
