<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoleAndPermission;
    use Notifiable;

    /**
     * The attributes that are fillable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_identifier', // either edu-ID primary ID or Username
        'display_name',
        'is_edu_id',
        'is_alma',
        'password',
        'last_login'
    ];

    /**
     * The attributes that are searchable via quicksearch.
     *
     * @var string[]
     */
    protected static $searchable = [
        'display_name',
        'user_identifier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Permissions relationship
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('roles.models.permission'))->withTimestamps();
    }

    /**
     * Check if user is SLSP Admin
     *
     * @return boolean
     */
    public function isSLSPAdmin(): bool
    {
        return $this->hasRole('slskeyadmin');
    }

    /**
     * Check if user has any permissions
     *
     * @return boolean
     */
    public function hasAnyPermissions(): bool
    {
        return $this->getPermissions()->count() > 0;
    }

    /**
     * Get number of permissions
     *
     * @return int
     */
    public function getNumberOfPermissions(): int
    {
        return $this->getPermissions()->count();
    }

    /**
     * Get the slskey_codes of SlskeyGroups that the User has permissions for.
     *
     * @return array
     */
    public function getSlskeyGroupPermissionsSlskeyCodes(): array
    {
        return $this->getPermissions()->map(function ($permission) {
            return $permission->slug;
        })->toArray();
    }

    /**
     * Get the SlskeyGroups that the User has permissions for.
     *
     * @return Collection
     */
    public function getSlskeyGroupsPermissions(): Collection
    {
        return $this->getPermissions()->map(function ($permission) {
            return SlskeyGroup::where('slskey_code', $permission->slug)->first();
        });
    }

    /**
     * Get the SlskeyGroup IDs that the User has permissions for.
     *
     * @return array
     */
    public function getSlskeyGroupsPermissionsIds(): array
    {
        return $this->getPermissions()->map(function ($permission) {
            return SlskeyGroup::where('slskey_code', $permission->slug)->first()->id;
        })->toArray();
    }

    /**
     * Set Password
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->password_change_at = Carbon::now();
        $this->save();
    }

    /**
     * Reset Password (remove password_change_at)
     *
     * @param string $password
     * @return void
     */
    public function resetPassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->password_change_at = null;
        $this->save();
    }

    /**
     * Update Last Login
     *
     * @return void
     */
    public function updateLastLogin(): void
    {
        $this->last_login = now();
        $this->save();
    }

    /**
     * Get the SlskeyGroup IDs that the User has permissions for.
     *
     * @return array
     */
    public function setSLSPAdmin(): void
    {
        $role = config('roles.models.role')::where('slug', '=', 'slskeyadmin')->first();
        $this->attachRole($role);
    }

    /**
     * Remove SLSP Admin role
     *
     * @return void
     */
    public function removeSLSPAdmin(): void
    {
        $role = config('roles.models.role')::where('slug', '=', 'slskeyadmin')->first();
        $this->detachRole($role);
    }

    /**
     * Give permissions to user
     *
     * @param string $slskeyCode
     * @return void
     */
    public function givePermissions(string $slskeyCode): void
    {
        // Give permissions
        $permission = config('roles.models.permission')::where('slug', '=', $slskeyCode)->first();
        $user = User::where('user_identifier', '=', $this->user_identifier)->first();
        $user->attachPermission($permission);
    }

    /**
     * Remove permissions from user
     *
     * @param string $slskeyCode
     * @return void
     */
    public function removePermissions(string $slskeyCode): void
    {
        // Give permissions
        $permission = config('roles.models.permission')::where('slug', '=', $slskeyCode)->first();
        $user = User::where('user_identifier', '=', $this->user_identifier)->first();
        $user->detachPermission($permission);
    }

    /**
     * Remove all permissions from user
     *
     * @return void
     */
    public function removeAllPermissions(): void
    {
        $this->detachAllPermissions();
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
        ------    Search Filter -------
        */
        $query->when($filters['search'] ?? null, function ($query, $search) use ($searchableColumns) {
            $query->where(function ($query) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $query->orWhere($column, 'LIKE', '%'.$search.'%');
                }
            });
        });

        /*
        ------    SLSKey Group -------
        */
        $query->when($filters['slskeyCode'] ?? null, function ($query, $slskeyGroup) {
            $query->whereHas('permissions', function ($query) use ($slskeyGroup) {
                $query->where('slug', $slskeyGroup);
            });
        });

        return $query;
    }
}
