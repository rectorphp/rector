<?php

declare(strict_types=1);

namespace Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\Source;

use MyCLabs\Enum\Enum;

/**
 * @method SomeEnum VALUE()
 */
final class SomeEnum extends Enum
{
    const VALUE = 'value';
}
