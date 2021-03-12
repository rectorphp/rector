<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class FixtureHas
{
    /**
     * @var bool
     */
    private $full = false;

    public function hasFull()
    {
        return $this->full;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class FixtureHas
{
    /**
     * @var bool
     */
    private $hasFull = false;

    public function hasFull()
    {
        return $this->hasFull;
    }
}

?>
