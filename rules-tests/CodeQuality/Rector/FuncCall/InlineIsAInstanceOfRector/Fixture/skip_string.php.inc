<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector\Fixture;

final class SkipString
{
    public function run(object $object)
    {
        return is_a($object, 'SomeType');
    }
}
