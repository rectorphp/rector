<?php declare (strict_types=1);

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeAnotherTrait;
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
}
