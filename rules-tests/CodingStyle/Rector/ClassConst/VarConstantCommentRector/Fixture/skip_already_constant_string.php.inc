<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class SkipAlreadyConstantString
{
    /**
     * @var string[]
     *
     * Blemc
     */
    private const POSSIBLE_DELIMITERS = ['#', '~', '/'];
}
