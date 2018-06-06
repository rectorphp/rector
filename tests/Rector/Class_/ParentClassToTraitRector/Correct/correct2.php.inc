<?php declare (strict_types=1);

use Rector\Tests\Rector\Class_\ParentClassToTraitRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitRector\Source\SomeTrait;
    use AnotherTrait;
}
