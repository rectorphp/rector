<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\TypedPropertyRector\Wrong;

use Rector\Php\Tests\Rector\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    /**
     * @var AnotherClass|null
     */
    private $anotherClass = null;

    /**
     * @var null|AnotherClass
     */
    private $yetAnotherClass;
}
