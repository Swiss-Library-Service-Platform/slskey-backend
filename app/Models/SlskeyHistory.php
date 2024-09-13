<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlskeyHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'slskey_user_id',
        'slskey_group_id',
        'action',
        'author',
        'trigger',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected static $searchable = [
        'primary_id',
        'action',
        'author',
        'trigger',
    ];

    /**
     * Get the SlskeyUser that the SlskeyHistory belongs to.
     *
     * @return BelongsTo
     */
    public function slskeyUser(): BelongsTo
    {
        return $this->belongsTo(SlskeyUser::class);
    }

    /**
     * Get the SlskeyGroup that the SlskeyHistory belongs to.
     *
     * @return BelongsTo
     */
    public function slskeyGroup(): BelongsTo
    {
        return $this->belongsTo(SlskeyGroup::class);
    }

    /**
     * Set the SlskeyUserId of the SlskeyHistory.
     *
     * @param string $slskeyUserId
     * @return self
     */
    public function setSlskeyUserId(string $slskeyUserId): self
    {
        $this->slskey_user_id = $slskeyUserId;
        $this->save();

        return $this;
    }

    /**
     * Filter
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        /*
        ------    Search User Filter -------
        */
        $query->when($filters['primaryId'] ?? null, function ($query, $primaryId) {
            $query->where('primary_id', 'like', '%' . $primaryId . '%');
        });

        /*
        ------    Date Filter -------
        */
        $query->when($filters['date'] ?? null, function ($query, $date) {
            $query->whereDate('created_at', $date);
        });

        /*
        ------    SLSKeyCode Filter -------
        */
        $query->when($filters['slskeyCode'] ?? null, function ($query, $slskeyCode) {
            $query->whereHas('slskeyGroup', function ($query) use ($slskeyCode) {
                $query->where('slskey_code', $slskeyCode);
            });
        });

        /*
        ------    Trigger Filter -------
        */
        $query->when($filters['trigger'] ?? null, function ($query, $trigger) {
            $query->where('trigger', $trigger);
        });

        return $query;
    }
}
