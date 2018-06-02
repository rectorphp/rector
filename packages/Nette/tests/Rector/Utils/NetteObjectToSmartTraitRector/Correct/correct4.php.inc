<?php declare (strict_types=1);

use Rector\Nette\Tests\Rector\Utils\NetteObjectToSmartTraitRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Nette\Tests\Rector\Utils\NetteObjectToSmartTraitRector\Source\SomeTrait;
    public $property;
}
