<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcuadorSimulatorRecord extends Model
{
    protected $fillable = [
        'visitor_key', 'submission_id', 'user_id', 'province', 'subject', 'is_custom_subject',
        'first_cents', 'second_cents', 'required_third_cents', 'maximum_average_cents',
        'target_total_cents', 'status',
    ];

    protected $hidden = ['visitor_key', 'submission_id', 'user_id'];

    protected function casts(): array
    {
        return [
            'is_custom_subject' => 'boolean',
            'first_cents' => 'integer',
            'second_cents' => 'integer',
            'required_third_cents' => 'integer',
            'maximum_average_cents' => 'integer',
            'target_total_cents' => 'integer',
        ];
    }
}
