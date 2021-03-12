<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class FixtureWas
{
    /**
     * @var bool
     */
    private $full = false;

    public function wasFull()
    {
        return $this->full;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class FixtureWas
{
    /**
     * @var bool
     */
    private $wasFull = false;

    public function wasFull()
    {
        return $this->wasFull;
    }
}

?>
