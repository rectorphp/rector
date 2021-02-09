<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class InjectProperty extends AbstractParentPresenter
{
    /**
     * @inject
     */
    private \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $anotherClass;
}

abstract class AbstractParentPresenter
{

}

?>
