<?php

declare(strict_types=1);

namespace Rector\Visibility\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Source;

class ParentObject
{
    public const TO_BE_PUBLIC_CONSTANT = 1;
    protected const TO_BE_PROTECTED_CONSTANT = 2;
    private const TO_BE_PRIVATE_CONSTANT = 3;
}
