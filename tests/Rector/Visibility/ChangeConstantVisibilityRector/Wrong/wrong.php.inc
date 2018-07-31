<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source;

use Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector\Source\ParentObject;

class ClassWithInvalidConstants extends ParentObject
{
    private const TO_BE_PUBLIC_CONSTANT = 1;
    public const TO_BE_PROTECTED_CONSTANT = 2;
    protected const TO_BE_PRIVATE_CONSTANT = 3;
}
