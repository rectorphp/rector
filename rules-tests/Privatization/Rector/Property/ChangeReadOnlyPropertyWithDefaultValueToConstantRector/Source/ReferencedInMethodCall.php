<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Source;

class ReferencedInMethodCall
{
    public function process(array &$value)
    {
    }
}
