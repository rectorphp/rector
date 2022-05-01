<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Enum;

use RectorPrefix20220501\MyCLabs\Enum\Enum;
/**
 * @method static PreferenceSelfThis PREFER_THIS()
 * @method static PreferenceSelfThis PREFER_SELF()
 */
final class PreferenceSelfThis extends \RectorPrefix20220501\MyCLabs\Enum\Enum
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
