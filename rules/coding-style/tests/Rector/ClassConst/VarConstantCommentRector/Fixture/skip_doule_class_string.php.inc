<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

use Nette\Utils\Strings;
use PHPStan\TrinaryLogic;

final class SkipDoubleClassString
{
    /**
     * @var array<class-string>
     */
    private const ALLOWED_CLASS_TYPES = [Strings::class, TrinaryLogic::class];
}
