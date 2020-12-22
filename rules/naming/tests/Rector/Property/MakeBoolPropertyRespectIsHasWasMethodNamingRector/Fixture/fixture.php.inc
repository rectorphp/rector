<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $full = false;

    public function isFull()
    {
        return $this->full;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isFull = false;

    public function isFull()
    {
        return $this->isFull;
    }
}

?>
