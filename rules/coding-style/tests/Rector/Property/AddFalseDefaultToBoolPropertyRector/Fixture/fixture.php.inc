<?php

namespace Rector\CodingStyle\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isDisabled = false;
}

?>
