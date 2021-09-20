<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass as Alias;

final class UseAliasOnly2
{
    /**
     * @var Alias|\stdClass
     */
    private $anotherClass;

    public function __construct(Alias $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function setStdClass()
    {
        $this->anotherClass = new \stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass as Alias;

final class UseAliasOnly2
{
    private Alias|\stdClass $anotherClass;

    public function __construct(Alias $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function setStdClass()
    {
        $this->anotherClass = new \stdClass;
    }
}

?>
