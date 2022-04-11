<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class SkipVarAnnotationWhenItAlreadyIncludesNull
{
    /**
     * @var \DateTime[]|null
     */
    private ?array $dateTimes;
}
