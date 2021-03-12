<?php

namespace Rector\Tests\Order\Rector\Class_\OrderFirstLevelClassStatementsRector\Fixture;

final class SameNames
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

namespace Rector\Tests\Order\Rector\Class_\OrderFirstLevelClassStatementsRector\Fixture;

final class SameNames
{
    protected $name;
    protected $anotherName;
    public function name()
    {
    }
}

?>
