<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogJob;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogJobController extends Controller
{
    /**
     * Index route for Admin SLSKey History
     *
     * @return Response
     */
    public function index(): Response
    {
        $perPage = intval(Request::input('perPage', 5));

        $logs = LogJob::query()
            ->filter(Request::all())
            ->orderBy('logged_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Get all possible job values for filter dropdown
        $jobNameValues = LogJob::select('job')->distinct()->get()->map(function ($log) {
            return [
                'name' => $log->job,
                'value' => $log->job,
            ];
        });

        return Inertia::render('LogJobs/LogJobsIndex', [
            'logs' => $logs,
            'filters' => Request::all(),
            'jobOptions' => $jobNameValues,
        ]);
    }
}
