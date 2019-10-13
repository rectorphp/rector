<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source;

final class Token
{
    public static function create($value, $value2)
    {
        return new Contract\Token();
    }
}
