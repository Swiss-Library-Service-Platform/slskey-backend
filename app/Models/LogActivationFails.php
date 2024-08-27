<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivationFails extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_activation_fails';

    /**
     * The attributes that are fillable
     *
     * @var array
     */
    protected $fillable = [
        'primary_id',
        'action',
        'message',
        'author',
        'logged_at',
    ];

    public $timestamps = false;
}
