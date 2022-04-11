<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class SkipBrokenVarAnnotation
{
    /** @phpstan-var 'week'|'month'|'year' */
    private ?string $interval;
}
