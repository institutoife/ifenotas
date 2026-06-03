<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivePrizeWinner extends Model
{
    protected $fillable = [
        'winner_name',
        'prize',
        'drawn_number',
    ];
}
