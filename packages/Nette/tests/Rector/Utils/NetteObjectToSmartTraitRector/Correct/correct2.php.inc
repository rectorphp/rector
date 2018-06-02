<?php declare (strict_types=1);

class ClassWithExternalConstant
{
    use \Rector\Nette\Tests\Rector\Utils\NetteObjectToSmartTraitRector\Source\SomeTrait;
    use AnotherTrait;
}
