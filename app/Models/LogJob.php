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

    /**
     * The attributes that should be cast to native types
     *
     * @var array
     */
    protected $casts = [
        'info' => 'array', // Automatically cast the "info" column to an array (or object)
        'has_fail' => 'boolean', // Automatically cast the "has_fail" column to a boolean
    ];

    public $timestamps = false;

    /**
     * Filter the query based on the given filters
     *
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['job'] ?? null, function ($query, $job) {
            $query->where('job', $job);
        });

        $query->when($filters['has_fail'] ?? null, function ($query, $hasFail) {
            $query->where('has_fail', $hasFail == 'true');
        });

        return $query;
    }
}
