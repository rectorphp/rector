<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

final class SkipEndFunction
{
    private $values = ['1.2'];

    public function run($value): string
    {
        return $value ?? \end($this->values);
    }
}
