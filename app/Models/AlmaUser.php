<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class AlmaUser extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'alma_iz',

        'primary_id',
        'first_name',
        'last_name',
        'full_name',
        'preferred_language',
        'preferred_email',
        'addresses',

        // for webhook checks
        'user_identifier',
        'user_group',
        'email',

        // for staff users
        'record_type',
        'roles'
    ];

    /**
     * Get AlmaUser from API response
     *
     * @param stdClass $apiData
     * @return self
     */
    public static function fromApiResponse(stdClass $apiData): self
    {
        $preferredEmail = '';
        foreach ($apiData->contact_info->email as $email) {
            if ($email->preferred) {
                $preferredEmail = $email->email_address;
            }
        }
        // Find preferred language
        $language = $apiData->preferred_language->value;
        // Set to english if its an exotic language :-)
        if ($language != 'de' ||
            $language != 'en' ||
            $language != 'fr' ||
            $language != 'it') {
            $language = 'en';
        }

        // Get Record Type
        $recordType = $apiData->record_type->value;

        // Get Roles
        $roles = [];
        foreach ($apiData->user_role as $role) {
            if ($role->status->value == 'ACTIVE') {
                $roles[] = $role->role_type->value;
            }
        }

        return new self([
            'primary_id' => $apiData->primary_id,
            'first_name' => $apiData->first_name ?? null,
            'last_name' => $apiData->last_name,
            'full_name' => $apiData->full_name,
            'preferred_email' => $preferredEmail,
            'preferred_language' => $language,
            'addresses' => $apiData->contact_info->address ?? '',

            // for webhook checks
            'user_identifier' => $apiData->user_identifier,
            'user_group' => $apiData->user_group,
            'email' => $apiData->contact_info->email,

            // for staff users
            'record_type' => $recordType,
            'roles' => $roles
        ]);
    }

    /**
     * Get AlmaUser from JSON object
     *
     * @param array $jsonData
     * @return self
     */
    public static function fromJsonObject(array $jsonData): self
    {
        return new self([
            'primary_id' => $jsonData['primary_id'],
            'first_name' => $jsonData['first_name'],
            'last_name' => $jsonData['last_name'],
            'full_name' => $jsonData['full_name'],
            'preferred_language' => $jsonData['preferred_language'],
            'preferred_email' => $jsonData['preferred_email'],
            'addresses' => $jsonData['addresses'],
            'user_identifier' => $jsonData['user_identifier'],
            'user_group' => $jsonData['user_group'],
            'email' => $jsonData['email'],
            'record_type' => $jsonData['record_type'],
            'roles' => $jsonData['roles']
        ]);
    }
}
