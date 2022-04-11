<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class SkipVarAnnotationWithGenericSyntaxWhenNullIsNotMissing
{
    /**
     * @var array<int,\DateTime>|null
     */
    private ?array $dateTimes;
}
