<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Source;

final class Order
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';
}
