<?php

declare(strict_types=1);

namespace Rector\Core\Enum;

use MyCLabs\Enum\Enum;

/**
 * @see https://github.com/myclabs/php-enum
 *
 * @method static ObjectReference SELF()
 * @method static ObjectReference STATIC()
 * @method static ObjectReference PARENT()
 */
final class ObjectReference extends Enum
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
