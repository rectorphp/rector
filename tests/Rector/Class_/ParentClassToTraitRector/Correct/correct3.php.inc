<?php declare (strict_types=1);

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitRector\Source\SomeTrait;
    public $property;
}
