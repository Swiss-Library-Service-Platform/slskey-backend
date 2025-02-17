<?php

namespace App\Models;

use App\Interfaces\SwitchAPIInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SwitchGroup extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'switch_group_id',
        'publishers',
    ];

    /**
     * Get the SlskeyGroups that the SwitchGroup belongs to.
     *
     * @return BelongsToMany
     */
    public function slskeyGroups(): BelongsToMany
    {
        return $this->belongsToMany(SlskeyGroup::class);
    }

    /**
     * Get Switch Groups
     *
     * @return BelongsToMany
     */
    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(Publisher::class);
    }

    /**
     * Get the publishers array from the publishers string
     *
     * @return array
     */
    public function getPublisherArrayFromPublisherString(): array
    {
        return explode(';', $this->publishers);
    }

    /**
     * Get the members count for the group
     *
     * @param SwitchAPIInterface $switchApiService
     * @return integer
     */
    public function membersCount(SwitchAPIInterface $switchApiService): int
    {
        $membersCount = 0;
        try {
            $membersCount = $switchApiService->getMembersCountForGroupId($this->switch_group_id);
        } catch (\Exception $e) {
            // Log error
        }

        return $membersCount;
    }

    /**
     * Filter Users
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (
            array_key_exists('slskeyCode', $filters)
        ) {
            $slskeyCode = $filters['slskeyCode'] ?? null;
            $query->whereHas('slskeyGroups', function ($query) use ($slskeyCode) {
                $query->where('slskey_code', $slskeyCode);
            });
        }

        if (
            array_key_exists('publisher', $filters)
        ) {
            $publisher = $filters['publisher'] ?? null;
            $query->where('publishers', 'like', '%' . $publisher . '%');
        }

        return $query;
    }
}
