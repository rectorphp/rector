<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source;

class AnotherClassWithInvalidConstants
{
    public const TO_BE_PRIVATE_CONSTANT = 1;
}
