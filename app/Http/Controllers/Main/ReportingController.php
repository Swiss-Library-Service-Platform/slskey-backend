<?php

namespace App\Http\Controllers\Main;

use App\Exports\ReportingExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupReportResource;
use App\Http\Resources\SlskeyGroupSelectResource;
use App\Models\ReportEmailAddress;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyReportCounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportingController extends Controller
{
    /**
     * Index route for Reporting
     *
     * @return Response
     */
    public function index(): Response
    {
        $data = $this->getReportData();

        return Inertia::render('Reporting/ReportingIndex', [
            'reportCounts' => $data['reportCounts'],
            'slskeyGroups' => SlskeyGroupSelectResource::collection($data['slskeyGroups']),
            'selectedSlskeyCode' => $data['selectedSlskeyCode'],
            'isAnyEducationalUsers' => $data['isAnyEducationalUsers'],
        ]);
    }

    /**
     * Export Reporting data to Excel
     *
     * @return Response
     */
    public function export()
    {
        $data = $this->getReportData();
        $reportCounts = $data['reportCounts']->toArray();

        return Excel::download(new ReportingExport($reportCounts, $data['isAnyEducationalUsers']), 'report.xlsx');
    }

    /**
     * Show Reporting settings for selected SLSKeyCode
     *
     * @param string $slskeyCode
     * @return Response
     */
    public function showReportSettings(string $slskeyCode): Response
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Check permissions for selected SLSKeyCode
        $slspEmployee = $user->isSLSPAdmin();

        if (!$slspEmployee) {
            $permissionIds = $user->getSlskeyGroupPermissionsSlskeyCodes();

            // Check permissions for selected SLSKeyCode
            if (!in_array($slskeyCode, $permissionIds)) {
                abort(403);
            }
        }

        // Get selected slskey group
        $slskeyGroup = SlskeyGroup::query()
            ->where('slskey_code', $slskeyCode)
            ->first();

        return Inertia::render('Reporting/ReportingSettings', [
            'slskeyGroup' => new SlskeyGroupReportResource($slskeyGroup),
        ]);
    }

    /**
     * Add Reporting Email for selected SLSKeyCode
     *
     * @param string $slskeyCode
     * @return RedirectResponse
     */
    public function addReportingEmail(string $slskeyCode): RedirectResponse
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Check permissions for selected SLSKeyCode
        $slspEmployee = $user->isSLSPAdmin();

        if (!$slspEmployee) {
            $permissionIds = $user->getSlskeyGroupPermissionsSlskeyCodes();

            // Check permissions for selected SLSKeyCode
            if (!in_array($slskeyCode, $permissionIds)) {
                abort(403);
            }
        }

        // Validate email
        $validated = Request::validate([
            'email' => 'required|email',
        ]);

        // Get SlskeyGroup from slskeyCode
        $slskeyGroup = SlskeyGroup::query()
            ->where('slskey_code', $slskeyCode)
            ->firstOrFail();

        // Create ReportEmail
        $reportEmail = ReportEmailAddress::create([
            'email_address' => $validated['email'],
            'slskey_group_id' => $slskeyGroup->id,
        ]);

        return Redirect::back()
            ->with('success', __('flashMessages.reportmail_created'));
    }

    /**
     * Remove Reporting Email for selected SLSKeyCode
     *
     * @param string $slskeyCode
     * @param string $emailId
     * @return RedirectResponse
     */
    public function removeReportingEmail(string $slskeyCode, string $emailId): RedirectResponse
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Check permissions for selected SLSKeyCode
        $slspEmployee = $user->isSLSPAdmin();

        if (!$slspEmployee) {
            $permissionIds = $user->getSlskeyGroupPermissionsSlskeyCodes();

            // Check permissions for selected SLSKeyCode
            if (!in_array($slskeyCode, $permissionIds)) {
                abort(403);
            }
        }

        // Get ReportEmail
        $reportEmail = ReportEmailAddress::query()
            ->whereHas('slskeyGroup', function ($query) use ($slskeyCode) {
                $query->where('slskey_code', $slskeyCode);
            })
            ->where('id', $emailId)
            ->firstOrFail();

        // Delete ReportEmail
        $reportEmail->delete();

        return Redirect::back()
            ->with('success', __('flashMessages.reportmail_deleted'));
    }

    /**
     * Get report data
     *
     * @return array
     */
    private function getReportData(): array
    {
        // Get permitted SlskeyGroups for User
        $slskeyGroups = SlskeyGroup::query()
            ->wherePermissions()
            ->get();

        $slskeyGroupIds = $slskeyGroups->pluck('id')->toArray();

        // Get selected SLSKeyCode
        $selectedSlskeyCode = Request::input('slskeyCode');
        $selectedSlskeyGroupId = null;

        if ($selectedSlskeyCode) {
            // Check if selected SLSKeyCode is existing
            $selectedSlskeyGroupId = SlskeyGroup::query()
                ->where('slskey_code', $selectedSlskeyCode)
                ->firstOrFail()
                ->id;

            // Check permissions for selected SLSKeyCode
            if (!in_array($selectedSlskeyGroupId, $slskeyGroupIds)) {
                abort(403);
            }
        }

        // Set filter to selected SLSKeyCode or all permitted SLSKeyCodes
        $selectedSlskeyGroupIds = $selectedSlskeyGroupId ? [$selectedSlskeyGroupId] : $slskeyGroupIds;
        if ($selectedSlskeyCode) {
            // Get the report counts for the selected SLSKeyCode
            $reportCounts = SlskeyReportCounts::query()
                ->whereIn('slskey_group_id', $selectedSlskeyGroupIds)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        } else {
            // Get all report counts and accumulate them
            $reportCounts = SlskeyReportCounts::query()
                ->whereIn('slskey_group_id', $selectedSlskeyGroupIds)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->groupBy('year', 'month')
                ->selectRaw('year, month, sum(activated_count) as activated_count, sum(extended_count) as extended_count, sum(reactivated_count) as reactivated_count, sum(deactivated_count) as deactivated_count, sum(blocked_active_count) as blocked_active_count, sum(blocked_inactive_count) as blocked_inactive_count, sum(monthly_change_count) as monthly_change_count, sum(total_active_users) as total_active_users, sum(total_active_educational_users) as total_active_educational_users')
                ->get();
        }

        // Add current month to report counts
        $currentMonthCounts = SlskeyReportCounts::getCurrentMonthCounts($selectedSlskeyGroupIds);
        $reportCounts->prepend($currentMonthCounts);

        $isAnyEducationalUsers = SlskeyActivation::whereIn('slskey_group_id', $selectedSlskeyGroupIds)->where('activated', 1)->where('member_educational_institution', 1)->exists();

        return [
            'reportCounts' => $reportCounts,
            'slskeyGroups' => $slskeyGroups,
            'selectedSlskeyCode' => $selectedSlskeyCode,
            'isAnyEducationalUsers' => $isAnyEducationalUsers,
        ];
    }
}
