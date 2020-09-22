<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class NoDocblock
{
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

class NoDocblock
{
    private $isFull = false;

    public function isFull()
    {
        return $this->isFull;
    }
}

?>
