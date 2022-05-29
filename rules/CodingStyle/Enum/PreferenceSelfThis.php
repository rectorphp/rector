<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Enum;

final class PreferenceSelfThis
{
    /**
     * @api
     * @var string
     */
    public const PREFER_THIS = 'prefer_this';
    /**
     * @api
     * @var string
     */
    public const PREFER_SELF = 'prefer_self';
    /**
     * @api
     * @deprecated Use direct constant
     */
    public static function PREFER_THIS() : string
    {
        return self::PREFER_THIS;
    }
    /**
     * @api
     * @deprecated Use direct constant
     */
    public static function PREFER_SELF() : string
    {
        return self::PREFER_SELF;
    }
}
