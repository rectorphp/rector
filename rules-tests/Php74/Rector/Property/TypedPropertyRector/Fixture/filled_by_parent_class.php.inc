<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\FillerAbstract;

class FilledByParentClass extends FillerAbstract
{
    /**
     * @var AnotherClass
     */
    protected $property;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\FillerAbstract;

class FilledByParentClass extends FillerAbstract
{
    protected ?\Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass $property = null;
}

?>
