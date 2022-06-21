<?php

namespace Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Fixture;

use PHPStan\Type\ArrayType;

final class SkipSimpleYieldArray
{
    public function run(ArrayType $arrayType)
    {
        yield [$arrayType, 'string[]'];
    }
}
