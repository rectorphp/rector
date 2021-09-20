<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass as Alias;

final class UseAliasOnly3
{
    private $anotherClass;

    public function __construct(Alias $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass as Alias;

final class UseAliasOnly3
{
    private Alias $anotherClass;

    public function __construct(Alias $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }
}

?>
