<?php

namespace App\Enums;

class PublisherProtocolEnums
{
    public const PROTOCOL_SAML_VALUE = 1;

    public const PROTOCOL_SAML_TEXT = 'SAML';

    public const PROTOCOL_OIDC_VALUE = 2;

    public const PROTOCOL_OIDC_TEXT = 'OIDC';

    public const PROTOCOL_SAML_OIDC_VALUE = 3;

    public const PROTOCOL_SAML_OIDC_TEXT = 'SAML & OIDC';

    /**
     * Get the keys.
     *
     * @return array
     */
    public static function getKeys(): array
    {
        return [
            self::PROTOCOL_SAML_VALUE => self::PROTOCOL_SAML_TEXT,
            self::PROTOCOL_OIDC_VALUE => self::PROTOCOL_OIDC_TEXT,
            self::PROTOCOL_SAML_OIDC_VALUE => self::PROTOCOL_SAML_OIDC_TEXT,
        ];
    }

    /**
     * Get the key.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function getKey(string $value): ?string
    {
        return self::getKeys()[$value] ?? null;
    }
}
