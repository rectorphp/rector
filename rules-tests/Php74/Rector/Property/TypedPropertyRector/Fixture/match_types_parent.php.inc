<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

class PropperParent
{

}

final class MatchTypesParent extends PropperParent
{
    /**
     * @var parent
     */
    private $j;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

class PropperParent
{

}

final class MatchTypesParent extends PropperParent
{
    private parent $j;
}

?>
