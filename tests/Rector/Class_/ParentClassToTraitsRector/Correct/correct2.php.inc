<?php declare (strict_types=1);

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Wrong;

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use AnotherTrait;
}
