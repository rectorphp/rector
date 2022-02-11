<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class NullableVarUnionClassLike
{
    /**
     * @var null|AnotherClass|\stdClass
     */
    private $anotherClass;

    public function __construct(?AnotherClass $anotherClass)
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

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class NullableVarUnionClassLike
{
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass|\stdClass|null $anotherClass;

    public function __construct(?AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function setStdClass()
    {
        $this->anotherClass = new \stdClass;
    }
}

?>
