<?php

namespace App\Http\Controllers\Main;

use App\Exports\ReportingExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupReportResource;
use App\Http\Resources\SlskeyGroupSelectResource;
use App\Models\ReportEmailAddress;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Models\SlskeyHistoryMonth;
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
        $slskeyGroupIds = $selectedSlskeyGroupId ? [$selectedSlskeyGroupId] : $slskeyGroupIds;

        // Get first Slskeyhistory activation month and year
        $firstHistory = SlskeyHistory::query()
            ->whereIn('slskey_group_id', $slskeyGroupIds)
            ->orderBy('created_at', 'asc')
            ->first();
        $firstDate = $firstHistory ? $firstHistory->created_at : date('Y-m-d');

        // Get SlskeyActivations
        $slskeyHistories = SlskeyHistoryMonth::getGroupedByMonthWithActionCounts($slskeyGroupIds, $firstDate);

        return Inertia::render('Reporting/ReportingIndex', [
            'slskeyHistories' => $slskeyHistories,
            'slskeyGroups' => SlskeyGroupSelectResource::collection($slskeyGroups),
            'selectedSlskeyGroup' => $selectedSlskeyCode,
            'firstDate' => $firstDate,
        ]);
    }

    /**
     * Export Reporting data to Excel
     *
     * @return Response
     */
    public function export()
    {
        // Get permitted SlskeyGroups for User
        $firstDate = Request::input('firstDate');

        // Get selected SLSKeyCode
        $selectedSlskeyCode = Request::input('slskeyCode');
        $selectedSlskeyGroupId = null;

        if ($selectedSlskeyCode) {
            // Check if selected SLSKeyCode is existing
            $selectedSlskeyGroupId = SlskeyGroup::where('slskey_code', $selectedSlskeyCode)
                ->wherePermissions()
                ->firstOrFail()
                ->id;
        }
        $slskeyGroupIds = $selectedSlskeyGroupId ? [$selectedSlskeyGroupId] : SlskeyGroup::wherePermissions()->get()->pluck('id')->toArray();

        return Excel::download(new ReportingExport($slskeyGroupIds, $firstDate), 'report.xlsx');
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
}
