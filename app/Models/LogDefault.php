<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class LogDefault extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_default';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'context' => AsArrayObject::class,
        'extra' => AsArrayObject::class,
    ];
}
