<?php

declare (strict_types=1);
namespace Rector\Core\Enum;

use RectorPrefix20220501\MyCLabs\Enum\Enum;
/**
 * @see https://github.com/myclabs/php-enum
 *
 * @method static ObjectReference SELF()
 * @method static ObjectReference STATIC()
 * @method static ObjectReference PARENT()
 */
final class ObjectReference extends \RectorPrefix20220501\MyCLabs\Enum\Enum
{
    /**
     * @var string
     */
    private const SELF = 'self';
    /**
     * @var string
     */
    private const PARENT = 'parent';
    /**
     * @var string
     */
    private const STATIC = 'static';
}
