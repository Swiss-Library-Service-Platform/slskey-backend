<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

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
        $searchableColumns = static::$searchable;

        /*
        ------    Search Filter -------
        */
        $query->when($filters['search'] ?? null, function ($query, $search) use ($searchableColumns) {
            $searchTerms = explode(' ', $search);
            $query->where(function ($query) use ($searchTerms, $searchableColumns) {
                foreach ($searchTerms as $term) {
                    $query->where(function ($query) use ($term, $searchableColumns) {
                        foreach ($searchableColumns as $column) {
                            $query->orWhere($column, 'LIKE', '%'.$term.'%');
                        }
                    });
                }
            });
        });

        /*
            Sort by filters
        */

        $query->when($filters['sortBy'] ?? function ($query) {
            // When no order is set
            return $query->orderBy('created_at', 'asc');
        }, function ($query, $sort_by) use ($filters) {
            // Sort by activation date
            if ($sort_by == 'activation_date') {
                $permittedActivations = $query->get()->pluck('slskeyActivations')->flatten(); // we got these earlier when calling withPermittedActivations()
                if ($filters['sortAsc'] == 'false') {
                    // this is slow, but it works
                    $query->orderByRaw('(
                        SELECT max(activation_date)
                        FROM slskey_activations
                        WHERE slskey_user_id = slskey_users.id
                        AND slskey_group_id IN (' . $permittedActivations->pluck('slskey_group_id')->implode(',') . ')
                        group by slskey_user_id
                        ORDER BY activation_date DESC
                        LIMIT 1
                    ) DESC');
                } else {
                    // this is slow, but it works
                    $query->orderByRaw('(
                        SELECT max(COALESCE(activation_date, \'9999-12-31\'))
                        FROM slskey_activations
                        WHERE slskey_user_id = slskey_users.id
                        AND slskey_group_id IN (' . $permittedActivations->pluck('slskey_group_id')->implode(',') . ')
                        group by slskey_user_id
                        ORDER BY activation_date DESC
                        LIMIT 1
                    ) ASC');
                }
            }
            // Sort by expiration date
            if ($sort_by == 'expiration_date') {
                $permittedActivations = $query->get()->pluck('slskeyActivations')->flatten(); // we got these earlier when calling withPermittedActivations()
                if ($filters['sortAsc'] == 'false') {
                    // this is slow, but it works
                    $query->orderByRaw('(
                        SELECT max(expiration_date)
                        FROM slskey_activations
                        WHERE slskey_user_id = slskey_users.id
                        AND slskey_group_id IN (' . $permittedActivations->pluck('slskey_group_id')->implode(',') . ')
                        group by slskey_user_id
                        ORDER BY expiration_date DESC
                        LIMIT 1
                    ) DESC');
                } else {
                    // this is slow, but it works
                    $query->orderByRaw('(
                        SELECT max(COALESCE(expiration_date, \'9999-12-31\'))
                        FROM slskey_activations
                        WHERE slskey_user_id = slskey_users.id
                        AND slskey_group_id IN (' . $permittedActivations->pluck('slskey_group_id')->implode(',') . ')
                        group by slskey_user_id
                        ORDER BY expiration_date DESC
                        LIMIT 1
                    ) ASC');
                }
            }

            if ($sort_by == 'full_name') {
                $query->orderBy('first_name', $filters['sortAsc'] == 'true' ? 'asc' : 'desc')
                    ->orderBy('last_name', $filters['sortAsc'] == 'true' ? 'asc' : 'desc');
            }
        });

        if (
            array_key_exists('slskeyCode', $filters)
            || array_key_exists('status', $filters)
            || array_key_exists('activation_start', $filters)
            || array_key_exists('activation_end', $filters)
        ) {
            $slskeyCode = $filters['slskeyCode'] ?? null;
            $status = $filters['status'] ?? null;
            $activationStart = $filters['activation_start'] ?? null;
            $activationEnd = $filters['activation_end'] ?? null;

            $query->whereHas('slskeyActivations', function ($query) use ($status, $slskeyCode, $activationStart, $activationEnd) {
                // Check for permissions again here, otherwise it looks up all users/activations for given filters

                /** @var \App\Models\User */
                $user = Auth::user();
                $slspEmployee = $user->isSLSPAdmin();
                if (! $slspEmployee) {
                    $permissions = $user->getSlskeyGroupsPermissionsIds();
                    $query = $query->whereHas('slskeyGroup', function ($query) use ($permissions) {
                        $query->whereIn('id', $permissions);
                    });
                }
                /*
                ------    SLSKey Code Filter -------
                */
                if ($slskeyCode) {
                    $query->whereHas('slskeyGroup', function ($query) use ($slskeyCode) {
                        $query->where('slskey_code', $slskeyCode);
                    });
                }

                /*
                ------    Status Filter -------
                */
                if ($status) {
                    if ($status === 'ACTIVE') {
                        $query->where('activated', 1);
                    } elseif ($status === 'DEACTIVATED') {
                        $query->where('activated', 0)->where('blocked', 0);
                    } elseif ($status === 'BLOCKED') {
                        $query->where('blocked', 1);
                    }
                }

                /*
                ------    Activation Date Filter -------
                */
                if ($activationStart) {
                    $query->whereDate('activation_date', '>=', $activationStart);
                }
                if ($activationEnd) {
                    $query->whereDate('activation_date', '<=', $activationEnd);
                }
            });
        }

        return $query;
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

        // SLSP Super Admin
        /** @var \App\Models\User */
        $user = Auth::user();
        $slspEmployee = $user->isSLSPAdmin();
        if (! $slspEmployee) {
            // SLSKey Group Permissions
            $permissions = $user->getSlskeyGroupsPermissionsIds();

            // Filter for users with permitted activation
            $query->whereHas('slskeyActivations.slskeyGroup', function ($query) use ($permissions) {
                $query->whereIn('id', $permissions);
            });
        }

        return $query;
    }

    /**
     * Get Users With their permitted Activations
     *
     * @param Builder $query
     * @param string|null $slskeyCode
     * @return Builder
     */
    public function scopeWithPermittedActivations(Builder $query, ?string $slskeyCode = null): Builder
    {
        // Affiliate slskeyActivations, but only desired ones
        $query->with([
            'slskeyActivations' => function ($query) use ($slskeyCode) {
                // Optionally Filter by SLSKey code
                if ($slskeyCode) {
                    $query->whereHas('slskeyGroup', function ($query) use ($slskeyCode) {
                        $query->where('slskey_code', $slskeyCode);
                    });
                }

                // Filter for permitted activations
                /** @var \App\Models\User */
                $user = Auth::user();
                $slspEmployee = $user->isSLSPAdmin();
                if (! $slspEmployee) {
                    // SLSKey Group Permissions
                    $permissions = $user->getSlskeyGroupsPermissionsIds();

                    // Filter for users with permitted activation
                    $query->whereHas('slskeyGroup', function ($query) use ($permissions) {
                        $query->whereIn('id', $permissions);
                    });
                }

                // Define Fields to be Eager Loaded
                $query->with(['slskeyGroup:id,name,slskey_code,days_activation_duration,workflow,webhook_mail_activation,show_member_educational_institution']);
            },
        ]);

        // Return the query
        return $query;
    }

    /**
     * Get Users With their permitted Histories
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithPermittedHistories(Builder $query): Builder
    {
        // SLSP Super Admin
        /** @var \App\Models\User */
        $user = Auth::user();
        $slspEmployee = $user->isSLSPAdmin();
        if ($slspEmployee) {
            return $query->with(['slskeyHistories.slskeyGroup:id,name,slskey_code']);
        }

        // SLSKey Group Permissions
        $permissions = $user->getSlskeyGroupsPermissionsIds();

        // return only permitted activation of users
        return $query->with([
            'slskeyHistories' => function ($query) use ($permissions) {
                $query->whereIn('slskey_group_id', $permissions)->with(['slskeyGroup:id,name,slskey_code']);
            },
        ]);
    }
}
