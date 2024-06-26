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
        'primary_id', // When activation fails, no SlskeyUser is created, but we still want to keep the primary ID in history log
        'action',
        'author',
        'trigger',
        'success',
        'error_message',
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
        'success',
        'error_message',
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
     * Set the Success Flag of the SlskeyHistory.
     *
     * @param boolean $success
     * @return self
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        $this->save();

        return $this;
    }

    /**
     * Set the Error Message of the SlskeyHistory.
     *
     * @param string $message
     * @return self
     */
    public function setErrorMessage(string $message): self
    {
        $this->error_message = $message;
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
        $searchableColumns = static::$searchable;

        /*
        ------    Search User Filter -------
        */
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('primary_id', 'like', '%' . $search . '%');
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
