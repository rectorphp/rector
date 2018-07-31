<?php declare (strict_types=1);

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \SilverStripe\Core\Extensible;
    use \SilverStripe\Core\Injector\Injectable;
    use \SilverStripe\Core\Config\Configurable;
}
