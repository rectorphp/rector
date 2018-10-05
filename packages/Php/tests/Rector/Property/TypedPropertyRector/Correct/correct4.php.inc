<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Wrong;

use Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithStaticProperty
{
    private static iterable $iterable;
}
