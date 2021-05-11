<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class AutowiredProperty
{
    /**
     * @var AnotherClass
     */
    private $anotherClass;

    /**
     * @required
     */
    public function autowire(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class AutowiredProperty
{
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass $anotherClass;

    /**
     * @required
     */
    public function autowire(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }
}

?>
