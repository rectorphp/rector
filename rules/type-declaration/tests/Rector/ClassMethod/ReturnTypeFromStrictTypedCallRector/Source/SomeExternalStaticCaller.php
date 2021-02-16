<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source;

final class SomeExternalStaticCaller
{
    public static function getNumbers(): int
    {
        return 1000;
    }
}
