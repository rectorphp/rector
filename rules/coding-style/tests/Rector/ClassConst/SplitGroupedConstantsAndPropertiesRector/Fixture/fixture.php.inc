<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector\Fixture;

class Fixture
{
    const HI = true, AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt, $isIsThough;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector\Fixture;

class Fixture
{
    const HI = true;
    const AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt;
    /**
     * @var string
     */
    public $isIsThough;
}

?>
