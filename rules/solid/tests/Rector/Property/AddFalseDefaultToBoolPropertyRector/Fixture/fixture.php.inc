<?php

namespace Rector\SOLID\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isDisabled;
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isDisabled = false;
}

?>
