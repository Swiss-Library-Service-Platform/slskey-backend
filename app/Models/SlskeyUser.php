<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SlskeyUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'primary_id',
        'first_name',
        'last_name',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are searchable via quicksearch.
     *
     * @var string[]
     */
    protected static $searchable = [
        'primary_id',
        'first_name',
        'last_name',
    ];

    /**
     * Get Slskey Activations
     */
    public function slskeyActivations(): HasMany
    {
        return $this->hasMany(SlskeyActivation::class);
    }

    /**
     * Get Slskey Histories
     *
     * @return HasMany
     */
    public function slskeyHistories(): HasMany
    {
        return $this->hasMany(SlskeyHistory::class);
    }

    /**
     * Check if user has active activation
     *
     * @param integer $slskeyGroupId
     * @return boolean
     */
    public function hasActiveActivation(int $slskeyGroupId): bool
    {
        $slskeyActivation = $this->slskeyActivations()->where('slskey_group_id', $slskeyGroupId)->first();

        return $slskeyActivation && $slskeyActivation->activated;
    }

    /**
     * Check if user has active activation for switch group
     *
     * @param string $switchGroupId
     * @param integer $slskeyGroupId
     * @return boolean
     */
    public function hasActiveActivationForSwitchGroupViaDifferentGroup(string $switchGroupId, int $slskeyGroupId): bool
    {
        $otherSlskeyActivations = $this->slskeyActivations()->where('slskey_group_id', '!=', $slskeyGroupId)->get();
        if (! $otherSlskeyActivations) {
            return false;
        }
        foreach ($otherSlskeyActivations as $slskeyActivation) {
            if ($slskeyActivation->activated) {
                $slskeyGroup = $slskeyActivation->slskeyGroup;
                if ($slskeyGroup->switchGroups->contains('switch_group_id', $switchGroupId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update User Details
     *
     * @param AlmaUser $almaUser
     * @return void
     */
    public function updateUserDetails(AlmaUser $almaUser): void
    {
        $this->first_name = $almaUser->first_name;
        $this->last_name = $almaUser->last_name;
        $this->save();
    }

    /**
     * Get Webhook Activation Mail
     *
     * @param integer $slskeyGroupId
     * @return string|null
     */
    public function getWebhookActivationMail(int $slskeyGroupId): ?string
    {
        $slskeyActivation = $this->slskeyActivations()->where('slskey_group_id', $slskeyGroupId)->first();

        return $slskeyActivation ? $slskeyActivation->webhook_activation_mail : null;
    }

    /**
     * Remove Webhook Activation Mail
     *
     * @param integer $slskeyGroupId
     * @return void
     */
    public function removeWebhookActivationMail(int $slskeyGroupId): void
    {
        $slskeyActivation = $this->slskeyActivations()->where('slskey_group_id', $slskeyGroupId)->first();
        if ($slskeyActivation) {
            $slskeyActivation->webhook_activation_mail = null;
            $slskeyActivation->save();
        }
    }

    /**
     * Check if primaryId is eduId
     *
     * @param string $primaryId
     * @return boolean
     */
    public static function isPrimaryIdEduId(string $primaryId): bool
    {
        // check regex $EDU_ID_POSTFIX = '/eduid.ch$/';
        return (bool) preg_match('/eduid.ch$/', $primaryId);
    }

    /**
     * Check if user is blocked
     *
     * @param string $slskeyCode
     * @return boolean
     */
    public function isBlocked(string $slskeyCode): bool
    {
        $slskeyActivation = $this->slskeyActivations()->where('slskey_group_id', $slskeyCode)->first();

        return $slskeyActivation && $slskeyActivation->blocked;
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
        $filteredSlskeyCode = $filters['slskeyCode'] ?? null;

        return $query
            ->slskeyCodeAndPermissionFilter($filteredSlskeyCode)
            ->searchFilter($filters['search'] ?? null)
            ->statusFilter($filters, $filteredSlskeyCode)
            ->activationDateFilter($filters['activation_start'] ?? null, $filters['activation_end'] ?? null, $filteredSlskeyCode)
            ->applySorting($filters['sortBy'] ?? null, $filters['sortAsc'] ?? 'false', $filteredSlskeyCode);
    }

    /**
     * Apply permission + slskeyCode filters to a relation query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $permittedIds
     * @param string|null $slskeyCode
     * @return void
     */
    private static function applyPermissionAndCodeFilter($query, array $permittedIds, ?string $slskeyCode): void
    {
        $query->whereIn('slskey_group_id', $permittedIds);

        if ($slskeyCode) {
            $query->whereHas('slskeyGroup', function ($q) use ($slskeyCode) {
                $q->where('slskey_code', $slskeyCode);
            });
        }
    }

    /**
     * Search Filter 
     *
     * @param string|null $sortBy
     * @param string $sortAsc
     * @
     * @return Builder
     */
    public function scopeSearchFilter(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;

        $searchableColumns = static::$searchable;
        $searchTerms = explode(' ', $search);

        return $query->where(function ($query) use ($searchTerms, $searchableColumns) {
            foreach ($searchTerms as $term) {
                $query->where(function ($query) use ($term, $searchableColumns) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere($column, 'LIKE', '%' . $term . '%');
                    }
                });
            }
        });
    }

    /**
     * Status Filter
     *
     * @param Builder $query
     * @param string|null $filteredStatus
     * @param string|null $filteredSlskeyCode
     * @return Builder
     */
    public function scopeStatusFilter(Builder $query, array $filters, ?string $filteredSlskeyCode = null): Builder
    {
        if (!isset($filters['status'])) return $query;

        $status = $filters['status'];
        $permittedIds = Auth::user()->getSlskeyGroupsPermissionsIds();

        return $query->whereHas('slskeyActivations', function ($q) use ($status, $permittedIds, $filteredSlskeyCode) {

            self::applyPermissionAndCodeFilter($q, $permittedIds, $filteredSlskeyCode);
            
            if ($status === 'ACTIVE') {
                $q->where('activated', 1);
            } elseif ($status === 'DEACTIVATED') {
                $q->where('activated', 0)->where('blocked', 0);
            } elseif ($status === 'BLOCKED') {
                $q->where('blocked', 1);
            }
        });
    }

    /**
     * SLSKey Code Filter
     *
     * @param Builder $query
     * @param string|null $slskeyCode
     * @return Builder
     */
    public function scopeSlskeyCodeAndPermissionFilter(Builder $query, ?string $slskeyCode): Builder
    {
        $permittedIds = Auth::user()->getSlskeyGroupsPermissionsIds();

        return $query->whereHas('slskeyActivations', function ($q) use ($slskeyCode, $permittedIds) {
            self::applyPermissionAndCodeFilter($q, $permittedIds, $slskeyCode);
        });
    }

    /**
     * Activation Date Filter
     *
     * @param Builder $query
     * @param string|null $activationStart
     * @param string|null $activationEnd
     * @param string|null $filteredSlskeyCode
     * @return Builder
     */
    public function scopeActivationDateFilter(Builder $query, ?string $start, ?string $end, ?string $filteredSlskeyCode = null): Builder
    {
        if (!$start && !$end) return $query;

        $permittedIds = Auth::user()->getSlskeyGroupsPermissionsIds();

        return $query->whereHas('slskeyActivations', function ($q) use ($start, $end, $permittedIds) {
            
            self::applyPermissionAndCodeFilter($q, $permittedIds, $filteredSlskeyCode);

            if ($start) {
                $q->whereDate('activation_date', '>=', $start);
            }

            if ($end) {
                $q->whereDate('activation_date', '<=', $end);
            }
        });
    }

    /**
     * Apply Sorting
     *
     * @param Builder $query
     * @param string|null $sortBy
     * @param string $sortAsc
     * @param string|null $filteredSlskeyCode
     * @return Builder
     */
    public function scopeApplySorting(Builder $query, ?string $sortBy, string $sortAsc, ?string $slskeyCode = null): Builder
    {
        $asc = $sortAsc === 'true';
        $permittedIds = Auth::user()->getSlskeyGroupsPermissionsIds();

        if ($sortBy === 'activation_date' || $sortBy === 'expiration_date') {
            $column = $sortBy === 'activation_date' ? 'activation_date' : 'expiration_date';
            $alias = $sortBy === 'activation_date' ? 'a' : 'e';
            $dateAlias = "latest_{$column}";

            $subquery = DB::table('slskey_activations')
                ->select('slskey_user_id', DB::raw("MAX({$column}) as {$dateAlias}"))
                ->whereIn('slskey_group_id', $permittedIds)
                ->when($slskeyCode, function ($q) use ($slskeyCode) {
                    $q->whereIn('slskey_group_id', function ($sub) use ($slskeyCode) {
                        $sub->select('id')
                            ->from('slskey_groups')
                            ->where('slskey_code', $slskeyCode);
                    });
                })
                ->groupBy('slskey_user_id');

            $query->leftJoinSub($subquery, $alias, function ($join) use ($alias) {
                $join->on('slskey_users.id', '=', "{$alias}.slskey_user_id");
            });

            $orderClause = $asc
                ? "{$alias}.{$dateAlias} IS NULL ASC, {$alias}.{$dateAlias} ASC"
                : "{$alias}.{$dateAlias} IS NULL ASC, {$alias}.{$dateAlias} DESC";

            return $query->orderByRaw($orderClause);
        }

        if ($sortBy === 'full_name') {
            return $query->orderBy('first_name', $asc ? 'asc' : 'desc')
                        ->orderBy('last_name', $asc ? 'asc' : 'desc');
        }

        return $query->orderBy('updated_at', 'desc');
    }


    /**
     * Get permitted Activations for Users
     *
     * @param Builder $query
     * @param string|null $slskeyCode
     * @return Builder
     */
    public function scopeWithPermittedActivations(Builder $query, ?string $slskeyCode = null): Builder
    {
        $permittedIds = Auth::user()->getSlskeyGroupsPermissionsIds();

        return $query->with([
            'slskeyActivations' => function ($q) use ($permittedIds, $slskeyCode) {
                self::applyPermissionAndCodeFilter($q, $permittedIds, $slskeyCode);
                $q->with([
                    'slskeyGroup:id,name,slskey_code,days_activation_duration,workflow,webhook_mail_activation,show_member_educational_institution'
                ]);
            }
        ]);
    }

    /**
     * Get Users With their permitted Histories
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithPermittedHistories(Builder $query): Builder
    {
        // Filter by permissions
        /** @var \App\Models\User */
        $user = Auth::user();
        $permissions = $user->getSlskeyGroupsPermissionsIds();

        return $query->with([
            'slskeyHistories' => function ($query) use ($permissions) {
                $query->whereIn('slskey_group_id', $permissions)->with(['slskeyGroup:id,name,slskey_code']);
            },
        ]);
    }


    /**
     * Get Users that have permitted Activations
     *
     * @param Builder $query
     * @param string|null $slskeyCode
     * @return Builder
     */
    public function scopeWhereHasPermittedActivations(Builder $query, ?string $slskeyCode = null): Builder
    {
        // Only Users with Activations
        $query->whereHas('slskeyActivations');

        // Optionally Filter by SLSKey code
        if ($slskeyCode) {
            $query->whereHas('slskeyActivations.slskeyGroup', function ($query) use ($slskeyCode) {
                $query->where('slskey_code', $slskeyCode);
            });
        }

        // Filter by permissions
        /** @var \App\Models\User */
        $user = Auth::user();
        $permissions = $user->getSlskeyGroupsPermissionsIds();
        $query->whereHas('slskeyActivations.slskeyGroup', function ($query) use ($permissions) {
            $query->whereIn('id', $permissions);
        });

        return $query;
    }
}
