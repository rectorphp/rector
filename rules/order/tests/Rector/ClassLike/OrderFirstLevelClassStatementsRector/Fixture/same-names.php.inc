<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Fixture;

final class SameNameClass
{
    public function name()
    {
    }
    protected $name;
    protected $anotherName;
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Fixture;

final class SameNameClass
{
    protected $name;
    protected $anotherName;
    public function name()
    {
    }
}

?>
