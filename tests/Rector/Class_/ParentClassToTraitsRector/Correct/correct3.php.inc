<?php declare (strict_types=1);

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}
