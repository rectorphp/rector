<?php declare (strict_types=1);

use Rector\Tests\Rector\Contrib\Nette\Utils\NetteObjectToSmartTraitRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Contrib\Nette\Utils\NetteObjectToSmartTraitRector\Source\SomeTrait;
    public $property;
}
