<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\FixturePhp74;

class SomeClass
{
    private bool $full;

    public function isFull()
    {
        return $this->full;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\FixturePhp74;

class SomeClass
{
    private bool $isFull;

    public function isFull()
    {
        return $this->isFull;
    }
}

?>
