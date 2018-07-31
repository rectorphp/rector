<?php declare (strict_types=1);

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}
