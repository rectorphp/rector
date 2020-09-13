<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class NoDefaultValue
{
    /**
     * @var bool
     */
    private $full;

    public function isFull()
    {
        return $this->full;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class NoDefaultValue
{
    /**
     * @var bool
     */
    private $isFull;

    public function isFull()
    {
        return $this->isFull;
    }
}

?>
