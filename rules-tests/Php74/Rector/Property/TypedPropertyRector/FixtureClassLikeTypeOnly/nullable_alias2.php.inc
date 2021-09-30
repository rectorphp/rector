<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass as Alias;

final class NullableAlias2
{
    /**
     * @var Alias|null
     */
    private $anotherClass;

    public function __construct(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass as Alias;

final class NullableAlias2
{
    private ?Alias $anotherClass;

    public function __construct(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }
}

?>
