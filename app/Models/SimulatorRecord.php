<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulatorRecord extends Model
{
    protected $fillable = ['visitor_key', 'submission_id', 'user_id', 'first', 'second', 'subject', 'required_third', 'pass_score', 'status'];
}
