<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class SkipClassString
{
    /**
     * @var class-string
     */
    const HI = self::class;
}
