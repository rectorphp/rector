<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class SkipArraykeyWithNullCoalescingReturnMixed
{
    /** @param array<string, string|int|null> $information */
    public function __construct(private readonly array $information)
    {
    }

    /** @return bool|float|int|string|null */
    public function getValue(string $key): mixed
    {
        return $this->information[$key] ?? null;
    }
}

?>
