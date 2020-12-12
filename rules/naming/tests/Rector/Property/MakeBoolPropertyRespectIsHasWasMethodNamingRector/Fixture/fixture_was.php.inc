<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SomeClassWas
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

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class SomeClassWas
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
