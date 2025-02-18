<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupSelectResource;
use App\Http\Resources\SwitchGroupAdminDetailResource;
use App\Http\Resources\SwitchGroupAdminResource;
use App\Interfaces\SwitchAPIInterface;
use App\Models\SlskeyGroup;
use App\Models\SwitchGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class SwitchGroupsController extends Controller
{
    protected $switchApiService;

    /**
     * SwitchGroupsController constructor.
     *
     * @param SwitchAPIInterface $switchApiService
     */
    public function __construct(SwitchAPIInterface $switchApiService)
    {
        $this->switchApiService = $switchApiService;
    }

    /**
     * Index route for Admin Switch Groups
     *
     * @return Response
     */
    public function index(): Response
    {
        $switchGroups = SwitchGroup::query()->filter(Request::all())->get();

        return Inertia::render('SwitchGroups/SwitchGroupsIndex', [
            'switchGroups' => SwitchGroupAdminResource::collection($switchGroups),
            'slskeyGroups' => SlskeyGroupSelectResource::collection(SlskeyGroup::query()->get()),
            'filters' => Request::all(),
        ]);
    }

    /**
     * Show route for Admin Switch Groups
     *
     * @param string $id
     * @return Response
     */
    public function show(string $id): Response
    {
        $switchGroup = SwitchGroup::query()
            ->where('id', $id)
            ->firstOrFail();

        return Inertia::render('SwitchGroups/SwitchGroupsShow', [
            'switchGroup' => SwitchGroupAdminDetailResource::make($switchGroup, $this->switchApiService),
        ]);
    }

    /**
     * Create route for Admin Switch Groups
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render(
            'SwitchGroups/SwitchGroupsCreate',
            []
        );
    }

    /**
     * Store route for Admin Switch Groups
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        $switchGroup = SwitchGroup::create(Request::validate([
            'name' => ['required', 'max:255'],
            'switch_group_id' => ['required', 'max:255'],
            'publishers' => ['max:65535'],
        ]));

        return Redirect::route('admin.switchgroups.index')
            ->with('success', __('flashMessages.switch_group_saved'));
    }

    /**
     * Update route for Admin Switch Groups
     *
     * @param SwitchGroup $switchGroup
     * @return RedirectResponse
     */
    public function update(SwitchGroup $switchGroup): RedirectResponse
    {
        $switchGroup->update(Request::validate([
            'name' => ['required', 'max:255'],
            'switch_group_id' => ['required', 'max:255'],
            'publishers' => ['max:65535'],
        ]));

        return Redirect::route('admin.switchgroups.index')
            ->with('success', __('flashMessages.switch_group_updated'));
    }

    public function downloadPublishers()
    {
        $publishers = SwitchGroup::query()
            ->select('publishers')
            ->get()
            ->map(function ($switchGroup) {
                return $switchGroup->getPublisherArrayFromPublisherString();
            })
            // trim whitepsace
            ->flatten()
            ->map(function ($publisher) {
                return trim($publisher);
            })
            ->filter(function ($publisher) {
                return !str_contains(strtolower($publisher), 'offen');
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    
        $fileName = 'publishers_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];
    
        return response()->stream(function() use ($publishers) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel encoding
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write data
            foreach ($publishers as $publisher) {
                fputcsv($handle, [$publisher]);
            }
            
            fclose($handle);
        }, 200, $headers);
    }
}
