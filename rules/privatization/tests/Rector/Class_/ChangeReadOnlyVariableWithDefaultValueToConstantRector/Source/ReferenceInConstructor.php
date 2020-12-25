<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Source;

final class ReferenceInConstructor
{
    public function __construct(string &$value)
    {
    }
}
