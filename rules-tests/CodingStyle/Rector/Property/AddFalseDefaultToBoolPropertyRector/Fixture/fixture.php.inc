<?php

namespace Rector\Tests\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

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

namespace Rector\Tests\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isDisabled = false;
}

?>
