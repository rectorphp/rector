<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class InjectProperty extends AbstractParentPresenter
{
    /**
     * @inject
     * @var AnotherClass
     */
    private $anotherClass;
}

abstract class AbstractParentPresenter
{

}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class InjectProperty extends AbstractParentPresenter
{
    /**
     * @inject
     */
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass $anotherClass;
}

abstract class AbstractParentPresenter
{

}

?>
