<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class MethodNameAfterPrefixNotMatchesPropertyName
{
    /**
     * @var bool
     */
    private $full = false;

    public function isSomething()
    {
        return $this->full;
    }

}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Fixture;

class MethodNameAfterPrefixNotMatchesPropertyName
{
    /**
     * @var bool
     */
    private $isSomething = false;

    public function isSomething()
    {
        return $this->isSomething;
    }

}

?>
