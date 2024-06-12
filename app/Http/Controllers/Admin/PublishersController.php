<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublisherInternalResource;
use App\Http\Resources\SwitchGroupSelectResource;
use App\Models\Publisher;
use App\Models\SwitchGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublishersController extends Controller
{
    /**
     * Index route for Admin Publishers
     *
     * @return Response
     */
    public function index(): Response
    {
        $publishers = Publisher::query()->get();

        return Inertia::render('Publishers/PublishersIndex', [
            'publishers' => PublisherInternalResource::collection($publishers),
        ]);
    }

    /**
     * Show route for Admin Publishers
     *
     * @param Publisher $publisher
     * @return Response
     */
    public function show(Publisher $publisher): Response
    {
        $availableSwitchGroups = SwitchGroup::all();

        return Inertia::render('Publishers/PublishersShow', [
            'publisher' => PublisherInternalResource::make($publisher),
            'availableSwitchGroups' => SwitchGroupSelectResource::collection($availableSwitchGroups),
            'availableProtocolOptions' => Publisher::getProtocolOptions(),
            'availableStatusOptions' => Publisher::getStatusOptions(),
        ]);
    }

    /**
     * Create route for Admin Publishers
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render(
            'Publishers/PublishersCreate',
            [
                'availableSwitchGroups' => SwitchGroupSelectResource::collection(SwitchGroup::all()),
                'availableProtocolOptions' => Publisher::getProtocolOptions(),
                'availableStatusOptions' => Publisher::getStatusOptions(),
            ]
        );
    }

    /**
     * Store route for Admin Publishers
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        $publisher = Publisher::create($this->validateInput());
        $this->manageSwitchGroups($publisher);

        return Redirect::route('admin.publishers.index')
            ->with('success', __('flashMessages.publisher_created'));
    }

    /**
     * Update route for Admin Publishers
     *
     * @param Publisher $publisher
     * @return RedirectResponse
     */
    public function update(Publisher $publisher): RedirectResponse
    {
        $publisher->update($this->validateInput());
        $this->manageSwitchGroups($publisher);

        return Redirect::route('admin.publishers.index')
            ->with('success', __('flashMessages.publisher_updated'));
    }

    /**
     * Destroy route for Admin Publishers
     *
     * @param Publisher $publisher
     * @return RedirectResponse
     */
    public function destroy(Publisher $publisher): RedirectResponse
    {
        $publisher->delete();

        return Redirect::route('admin.publishers.index')
            ->with('success', __('flashMessages.publisher_deleted'));
    }

    /**
     * Validate input for Publisher
     *
     * @return array
     */
    private function validateInput(): array
    {
        $rules = [
            'name' => ['required', 'max:255'],
            'entity_id' => ['nullable', 'url'],
            'protocol' => ['nullable', 'integer', 'in:1,2,3'],
            'internal_note' => ['nullable', 'max:255'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'switchGroups' => ['array'],
            'switchGroups.*.id' => ['required', 'integer', 'exists:switch_groups,id'],
        ];

        return Request::validate($rules);
    }

    /**
     * Manage Switch Groups for Publisher
     *
     * @param Publisher $publisher
     * @return void
     */
    private function manageSwitchGroups(Publisher $publisher): void
    {
        $oldGroups = $publisher->switchGroups->pluck('id')->toArray();
        $newGroups = collect(Request::input('switchGroups', []))->map(function ($group) {
            return $group['id'];
        })->toArray();
        $groupsToRemove = array_diff($oldGroups, $newGroups);
        $groupsToAdd = array_diff($newGroups, $oldGroups);
        foreach ($groupsToAdd as $group) {
            $publisher->switchGroups()->attach($group);
        }
        foreach ($groupsToRemove as $group) {
            $publisher->switchGroups()->detach($group);
        }
    }
}
