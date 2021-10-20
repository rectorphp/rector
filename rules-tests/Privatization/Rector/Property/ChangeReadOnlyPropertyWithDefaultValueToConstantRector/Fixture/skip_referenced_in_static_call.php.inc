<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

final class SkipReferencedInStaticCall
{
    private $value = [];

    public function run()
    {
        self::process($this->value);
    }

    private static function process(array &$value)
    {
    }
}
