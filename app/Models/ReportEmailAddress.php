<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportEmailAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'email_address',
        'slskey_group_id',
    ];

    /**
     * Get Slskey Group
     *
     * @return BelongsTo
     */
    public function slskeyGroup(): BelongsTo
    {
        return $this->belongsTo(SlskeyGroup::class);
    }
}
