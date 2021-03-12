<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source;

final class ReturnSelfFromSourceNullable
{
    public static function fromEvent() : ?self
    {
        if (rand(0, 1)) {
            new self;
        }

        return null;
    }
}
