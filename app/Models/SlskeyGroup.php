<?php

namespace App\Models;

use App\Builders\SlskeyGroupBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class SlskeyGroup extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slskey_code',
        'workflow',
        'send_activation_mail',
        'alma_iz',
        'webhook_secret',
        'webhook_custom_verifier',
        'webhook_custom_verifier_class',
        'days_activation_duration',
        'days_expiration_reminder',
        'webhook_mail_activation',
        'webhook_mail_activation_domains',
        'webhook_mail_activation_days_send_before_expiry',
        'webhook_mail_activation_days_token_validity',
    ];

    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $searchable = [
        'name',
        'slskey_code',
        'alma_iz',
    ];

    /**
     * New Eloquent Builder
     *
     * @param [type] $query
     * @return SlskeyGroupBuilder
     */
    public function newEloquentBuilder($query): SlskeyGroupBuilder
    {
        return new SlskeyGroupBuilder($query);
    }

    /**
     * Get Switch Groups
     *
     * @return BelongsToMany
     */
    public function switchGroups(): BelongsToMany
    {
        return $this->belongsToMany(SwitchGroup::class);
    }

    /**
     * Get Report Email Addresses
     *
     * @return HasMany
     */
    public function reportEmailAddresses(): HasMany
    {
        return $this->hasMany(ReportEmailAddress::class);
    }

    /**
     * Get Active User Count
     *
     * @return integer
     */
    public function activeUserCount(): int
    {
        $slskeyActivations = SlskeyActivation::where('slskey_group_id', $this->id)->where('activated', 1)->get();

        return $slskeyActivations->count();
    }

    /**
     * Get Switch Group IDs
     *
     * @return array
     */
    public function getSwitchGroupIds(): array
    {
        return $this->switchGroups->pluck('switch_group_id')->toArray();
    }

    /**
     * Get Publisher Array
     *
     * @return array
     */
    public function getPublisherArray(): array
    {
        $publishers = $this->switchGroups->map(function ($switchGroup) {
            return $switchGroup->getPublisherArrayFromPublisherString();
        })->flatten()->values()->toArray();

        return $publishers;
    }

    /**
     * Check if a user is allowed to activate
     *
     * @param AlmaUser $almaUser
     * @return boolean
     */
    public function checkCustomVerificationForUser(AlmaUser $almaUser): bool
    {
        $verified = true;

        if ($this->webhook_custom_verifier) {
            // Dynamically call the helper
            $verified = app('App\\Helpers\\CustomWebhookVerifier\\Implementations\\' . $this->webhook_custom_verifier_class)->verify($almaUser);
        }

        return $verified;
    }

    /**
     * Get Permitted User Groups with User Activations
     *
     * @param string $primaryId
     * @param string $almaIz
     * @return Collection
     */
    public static function getPermittedGroupsWithUserActivations(string $primaryId, ?string $almaIz = null): Collection
    {
        $slskeyUser = SlskeyUser::query()
            ->where('primary_id', $primaryId)
            ->whereHasPermittedActivations()
            ->withPermittedActivations()
            ->firstOr(function () {
                return null;
            });

        $permittedSlskeyGroups = static::query()
            ->wherePermissions();

        if ($almaIz) {
            $permittedSlskeyGroups = $permittedSlskeyGroups->where('alma_iz', $almaIz);
        }

        return $permittedSlskeyGroups
            ->get()->map(function ($slskeyGroup) use ($slskeyUser) {
                $activation = $slskeyUser ? $slskeyUser->slskeyActivations->where('slskey_group_id', $slskeyGroup->id)->first() : null;

                return [
                    // for dropdown:
                    'name' => $slskeyGroup->name,
                    'value' => $slskeyGroup->slskey_code,
                    'workflow' => $slskeyGroup->workflow,
                    'activation' => $activation ?? null,
                ];
            });
    }

    /**
     * Get Available Workflows Options
     *
     * @return array
     */
    public static function getAvailableWorkflowsOptions(): array
    {
        return [
            ['value' => 'Webhook', 'name' => 'Webhook'],
            ['value' => 'Manual', 'name' => 'Manual'],
        ];
    }

    /**
     * Get Available Webhook Custom Verifiers Options
     *
     * @return array
     */
    public static function getAvailableWebhookCustomVerifiersOptions(): array
    {
        $customVerifiers = scandir(app_path('Helpers/CustomWebhookVerifier/Implementations'));
        $customVerifiers = array_filter($customVerifiers, function ($verifier) {
            return !in_array($verifier, ['.', '..', 'VerifyController.php']);
        });
        // create new array with the verifiers inside
        $customVerifiers = array_map(function ($verifier) {
            // remove .php from the name
            $verifier = str_replace('.php', '', $verifier);

            return [
                'value' => $verifier,
                'name' => $verifier,
            ];
        }, $customVerifiers);

        return array_values($customVerifiers);
    }

    /**
     * Get Available Webhook Mail Activation Domains Options
     *
     * @return array
     */
    public static function getAvailableWebhookMailActivationDomainsOptions(): array
    {
        // Get all available files from /storage/email_domain_lists
        $emailDomainLists = scandir(storage_path('app/email_domain_lists'));
        $emailDomainLists = array_filter($emailDomainLists, function ($domainList) {
            return !in_array($domainList, ['.', '..']);
        });
        // create new array with the verifiers inside
        $emailDomainLists = array_map(function ($domainList) {
            return [
                'value' => $domainList,
                'name' => $domainList,
            ];
        }, $emailDomainLists);

        return array_values($emailDomainLists);
    }

    public function checkActivationMailDefinedIfSendActivationMailIsTrue(): bool
    {
        if (!$this->send_activation_mail) {
            return true;
        }

        $baseDirectory = 'views/emails/activation/';
        try {
            $emailFiles = scandir(resource_path($baseDirectory . $this->slskey_code));
        } catch (\Exception $e) {
            return false;
        }

        // check that directories de,en,fr,it exist
        $languages = ['de', 'en', 'fr', 'it'];
        // Loop through each required directory
        foreach ($languages as $dir) {
            // Construct the full path to the directory
            $dirPath = resource_path($baseDirectory . $this->slskey_code . '/' . $dir);

            // Check if the directory exists
            if (!is_dir($dirPath)) {
                return false;
            }

            // Check if the required file exists in the directory
            $filePath = $dirPath . '/email.blade.php';
            if (!file_exists($filePath)) {
                return false;
            }
        }

        // If all checks pass, return true
        return true;
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

        return $query;
    }

}
