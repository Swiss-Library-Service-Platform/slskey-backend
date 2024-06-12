<?php

namespace App\Enums;

class PublisherStatusEnums
{
    public const STATUS_ENABLED_VALUE = 1;

    public const STATUS_ENABLED_TEXT = 'Enabled';

    public const STATUS_PENDING_VALUE = 2;

    public const STATUS_PENDING_TEXT = 'Pending';

    public const STATUS_AUTHPROXY_ENABLED_VALUE = 3;

    public const STATUS_AUTHPROXY_ENABLED_TEXT = 'Enabled via AuthProxy';

    /**
     * Get the keys.
     *
     * @return array
     */
    public static function getKeys(): array
    {
        return [
            self::STATUS_ENABLED_VALUE => self::STATUS_ENABLED_TEXT,
            self::STATUS_PENDING_VALUE => self::STATUS_PENDING_TEXT,
            self::STATUS_AUTHPROXY_ENABLED_VALUE => self::STATUS_AUTHPROXY_ENABLED_TEXT,
        ];
    }

    /**
     * Get the key.
     *
     * @param string $value
     * @return string|null
     */
    public static function getKey(string $value): ?string
    {
        return self::getKeys()[$value] ?? null;
    }
}
