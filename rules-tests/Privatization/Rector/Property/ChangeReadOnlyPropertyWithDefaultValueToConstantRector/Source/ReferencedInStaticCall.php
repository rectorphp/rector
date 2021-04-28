<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Source;

class ReferencedInStaticCall
{
    public static function process(array &$value)
    {
    }
}
