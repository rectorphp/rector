<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class SkipNonEmptyStringVarDoc
{
    /** @var non-empty-string */
    public const NAME = 'testing';

    /**
     * @return non-empty-string
     */
    public function run(): string
    {
        return self::NAME;
    }
}
