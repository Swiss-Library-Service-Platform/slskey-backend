<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SlskeyGroupBuilder extends Builder
{
    /**
     * Filter SLSKey Groups by Permissions of user.
     *
     * @return self
     */
    public function wherePermissions(): self
    {
        $slspEmployee = Auth::user()->isSLSPAdmin();
        $permissions = Auth::user()->getSlskeyGroupPermissionsSlskeyCodes();

        // SLSP Super Admin
        if ($slspEmployee) {
            return $this;
        }

        // SLSKey Group Permissions
        $permittedSlskeyGroups = $this;
        if (empty($permissions)) {
            return $this->where('id', '<', 0);
        }

        return $permittedSlskeyGroups->whereIn('slskey_code', $permissions);
    }
}
