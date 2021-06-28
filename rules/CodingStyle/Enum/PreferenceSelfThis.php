<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static PreferenceSelfThis PREFER_THIS()
 * @method static PreferenceSelfThis PREFER_SELF()
 */
final class PreferenceSelfThis extends Enum
{
    /**
     * @api
     * @var string
     */
    private const PREFER_THIS = 'prefer_this';

    /**
     * @api
     * @var string
     */
    private const PREFER_SELF = 'prefer_self';
}
