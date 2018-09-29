<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\TypedPropertyRector\Wrong;

use Rector\Php\Tests\Rector\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    private ?AnotherClass $anotherClass = null;

    private ?AnotherClass $yetAnotherClass;
}
