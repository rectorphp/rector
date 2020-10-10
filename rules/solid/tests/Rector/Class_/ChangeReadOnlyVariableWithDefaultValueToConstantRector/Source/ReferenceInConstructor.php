<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Source;

final class ReferenceInConstructor
{
    public function __construct(string &$value)
    {
    }
}
