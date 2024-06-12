<?php

namespace App\Models;

use App\Enums\PublisherProtocolEnums;
use App\Enums\PublisherStatusEnums;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Publisher extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'entity_id',
        'protocol',
        'internal_note',
        'status',
    ];

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
     * Get Protocol Options
     *
     * @return array
     */
    public static function getProtocolOptions(): array
    {
        return [
            [
                'name' => PublisherProtocolEnums::PROTOCOL_SAML_TEXT, // 'SAML'
                'value' => PublisherProtocolEnums::PROTOCOL_SAML_VALUE,
            ],
            [
                'name' => PublisherProtocolEnums::PROTOCOL_OIDC_TEXT, // 'OIDC'
                'value' => PublisherProtocolEnums::PROTOCOL_OIDC_VALUE,
            ],
            [
                'name' => PublisherProtocolEnums::PROTOCOL_SAML_OIDC_TEXT, // 'SAML & OIDC'
                'value' => PublisherProtocolEnums::PROTOCOL_SAML_OIDC_VALUE,
            ],
        ];
    }

    /**
     * Get Status Options
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            [
                'name' => PublisherStatusEnums::STATUS_ENABLED_TEXT, // 'Enabled'
                'value' => PublisherStatusEnums::STATUS_ENABLED_VALUE,
            ],
            [
                'name' => PublisherStatusEnums::STATUS_PENDING_TEXT, // 'Pending'
                'value' => PublisherStatusEnums::STATUS_PENDING_VALUE,
            ],
            [
                'name' => PublisherStatusEnums::STATUS_AUTHPROXY_ENABLED_TEXT, // 'Enabled via AuthProxy'
                'value' => PublisherStatusEnums::STATUS_AUTHPROXY_ENABLED_VALUE,
            ],
        ];
    }
}
