<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogJob extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_job';

    /**
     * The attributes that are fillable
     *
     * @var array
     */
    protected $fillable = [
        'job',
        'info',
        'has_fail',
        'logged_at',
    ];

    public $timestamps = false;
}
