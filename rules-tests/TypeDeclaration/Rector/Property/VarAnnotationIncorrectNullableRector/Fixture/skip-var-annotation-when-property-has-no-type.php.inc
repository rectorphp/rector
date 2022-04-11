<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class SkipVarAnnotationWhenPropertyHasNoType
{
    /**
     * @var \DateTime[]
     */
    private $dateTimes;
}
