<?php

declare(strict_types=1);

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Source;

use MyCLabs\Enum\Enum;

/**
 * @method VALUE()
 */
final class SomeEnum extends Enum
{
    const VALUE = 'value';
}
