<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogApi extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_api';

    /**
     * The attributes that are fillable
     *
     * @var array
     */
    protected $fillable = [
        'method',
        'url',
        'ip',
        'input',
        'headers',
        'logged_at',
    ];

    public $timestamps = false;
}
