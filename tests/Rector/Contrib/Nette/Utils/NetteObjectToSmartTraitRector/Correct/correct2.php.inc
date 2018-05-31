<?php declare (strict_types=1);

class ClassWithExternalConstant
{
    use \Rector\Tests\Rector\Contrib\Nette\Utils\NetteObjectToSmartTraitRector\Source\SomeTrait;
    use AnotherTrait;
}
