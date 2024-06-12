<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupSelectResource;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class SlskeyHistoryController extends Controller
{
    /**
     * Index route for Admin SLSKey History
     *
     * @return Response
     */
    public function index(): Response
    {
        $perPage = intval(Request::input('perPage', 10));

        // paginate
        $slskeyHistories = SlskeyHistory::query()
            ->with('slskeyUser', 'slskeyGroup')
            ->filter(Request::all())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Query for SlskeyGroups with specified permissions
        $slskeyGroups = SlskeyGroup::all();

        // Get all possible trigger values for filter dropdown
        $triggerValues = SlskeyHistory::select('trigger')->distinct()->get()->map(function ($trigger) {
            return [
                'name' => $trigger->trigger,
                'value' => $trigger->trigger,
            ];
        });

        return Inertia::render('SlskeyHistory/SlskeyHistoryIndex', [
            'slskeyHistories' => $slskeyHistories,
            'filters' => Request::all(),
            'slskeyGroups' => SlskeyGroupSelectResource::collection($slskeyGroups),
            'triggers' => $triggerValues,
        ]);
    }
}
