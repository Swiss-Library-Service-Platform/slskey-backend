<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupAdminDetailResource;
use App\Http\Resources\SlskeyGroupAdminResource;
use App\Http\Resources\SwitchGroupSelectResource;
use App\Models\SlskeyGroup;
use App\Models\SwitchGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;
use jeremykenedy\LaravelRoles\Models\Permission;

class SlskeyGroupsController extends Controller
{
    /**
     * Index route for Admin SLSKey Groups
     *
     * @return Response
     */
    public function index(): Response
    {
        $slskeyGroups = SlskeyGroup::query()->get();

        return Inertia::render('SlskeyGroups/SlskeyGroupsIndex', [
            'filters' => Request::all(),
            'slskeyGroups' => SlskeyGroupAdminResource::collection($slskeyGroups),
        ]);
    }

    /**
     * Show route for Admin SLSKey Groups
     *
     * @param string $slskeyCode
     * @return Response
     */
    public function show(string $slskeyCode): Response
    {
        $slskeyGroup = SlskeyGroup::query()
            ->where('slskey_code', $slskeyCode)
            ->firstOrFail();
        $availableSwitchGroups = SwitchGroup::all();

        return Inertia::render('SlskeyGroups/SlskeyGroupsShow', [
            'slskeyGroup' => SlskeyGroupAdminDetailResource::make($slskeyGroup),
            'availableSwitchGroups' => SwitchGroupSelectResource::collection($availableSwitchGroups),
            'availableWorkflows' => SlskeyGroup::getAvailableWorkflowsOptions(),
            'availableWebhookCustomVerifiers' => SlskeyGroup::getAvailableWebhookCustomVerifiersOptions(),
            'availableWebhookMailActivationDomains' => SlskeyGroup::getAvailableWebhookMailActivationDomainsOptions(),
        ]);
    }

    /**
     * Create route for Admin SLSKey Groups
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('SlskeyGroups/SlskeyGroupsCreate', [
            'availableSwitchGroups' => SwitchGroupSelectResource::collection(SwitchGroup::all()),
            'availableWorkflows' => SlskeyGroup::getAvailableWorkflowsOptions(),
            'availableWebhookCustomVerifiers' => SlskeyGroup::getAvailableWebhookCustomVerifiersOptions(),
            'availableWebhookMailActivationDomains' => SlskeyGroup::getAvailableWebhookMailActivationDomainsOptions(),
        ]);
    }

    /**
     * Store route for Admin SLSKey Groups
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        $slskeyGroup = SlskeyGroup::create($this->validateInput());
        $this->manageSwitchGroups($slskeyGroup);

        // Create Permission
        config('roles.models.permission')::create([
            'name' => 'slskey group '.$slskeyGroup->slskey_code,
            'slug' => $slskeyGroup->slskey_code,
            'description' => 'Permission for SLSKey Group '.$slskeyGroup->slskey_code,
            'model' => 'SLSKeyGroup',
        ]);

        return Redirect::route('admin.groups.index')
            ->with('success', __('flashMessages.slskey_group_saved'));
    }

    /**
     * Update route for Admin SLSKey Groups
     *
     * @param SlskeyGroup $slskeyGroup
     * @return RedirectResponse
     */
    public function update(SlskeyGroup $slskeyGroup): RedirectResponse
    {
        $slskeyGroup->update($this->validateInput());
        $this->manageSwitchGroups($slskeyGroup);

        return Redirect::route('admin.groups.index')
            ->with('success', __('flashMessages.slskey_group_updated'));
    }

    /**
     * Destroy route for Admin SLSKey Groups
     *
     * @param SlskeyGroup $slskeyGroup
     * @return RedirectResponse
     */
    public function destroy(SlskeyGroup $slskeyGroup): RedirectResponse
    {
        // Delete Permission
        Permission::where('slug', $slskeyGroup->slskey_code)->delete();

        $slskeyGroup->delete();

        return Redirect::route('admin.groups.index')
            ->with('success', __('flashMessages.slskey_group_deleted'));
    }

    /**
     * Validate input
     *
     * @return array
     */
    private function validateInput(): array
    {
        $rules = [
            'name' => ['required', 'max:255'],
            // slskey code no spaces or special chars
            'slskey_code' => ['required', 'max:255', 'regex:/^[a-zA-Z0-9]+$/'],
            'workflow' => ['in:Webhook,Manual'],
            'send_activation_mail' => ['numeric'],
            'alma_iz' => ['required', 'max:12'],
        ];
        if (Request::input('workflow') === 'Webhook') {
            $rules = array_merge($rules, [
                'webhook_secret' => ['required', 'max:255'],
                'webhook_custom_verifier' => ['nullable', 'max:255'],
                'webhook_custom_verifier_class' => ['nullable', 'max:255'],
                'days_expiration_reminder' => ['nullable', 'integer', 'max:0'], // NULL or 0
            ]);

            // Webhook Mail Activation
            $rules['webhook_mail_activation'] = ['boolean'];
            if (Request::input('webhook_mail_activation')) {
                $rules['days_activation_duration'] = ['required', 'integer'];
                $rules['webhook_mail_activation_domains'] = ['required', 'max:255'];
                $rules['webhook_mail_activation_days_send_before_expiry'] = ['required', 'integer'];
                $rules['webhook_mail_activation_days_token_validity'] = ['required', 'integer'];
                $rules['webhook_custom_verifier'] = ['prohibited_if:webhook_mail_activation,1'];
            } else {
                $rules['days_activation_duration'] = ['prohibited'];
                $rules['webhook_mail_activation_domains'] = ['prohibited'];
                $rules['webhook_mail_activation_days_send_before_expiry'] = ['prohibited'];
                $rules['webhook_mail_activation_days_token_validity'] = ['prohibited'];
            }
        }
        if (Request::input('workflow') === 'Manual') {
            $rules = array_merge($rules, [
                'webhook_secret' => ['prohibited'],
                // webhook_custom_verifier: only int 0 allowed
                'webhook_custom_verifier' => ['nullable', 'integer', 'max:0'],
                'webhook_custom_verifier_class' => ['prohibited'],
                'days_expiration_reminder' => ['nullable', 'integer'],
                'days_activation_duration' => ['required', 'integer'],
            ]);
        }

        return Request::validate($rules);
    }

    /**
     * Manage Switch Groups
     *
     * @param SlskeyGroup $slskeyGroup
     * @return void
     */
    private function manageSwitchGroups(SlskeyGroup $slskeyGroup): void
    {
        $oldGroups = $slskeyGroup->switchGroups->pluck('id')->toArray();
        $newGroups = collect(Request::input('switchGroups', []))->map(function ($group) {
            return $group['id'];
        })->toArray();
        $groupsToRemove = array_diff($oldGroups, $newGroups);
        $groupsToAdd = array_diff($newGroups, $oldGroups);
        foreach ($groupsToAdd as $group) {
            $slskeyGroup->switchGroups()->attach($group);
        }
        foreach ($groupsToRemove as $group) {
            $slskeyGroup->switchGroups()->detach($group);
        }
    }
}
