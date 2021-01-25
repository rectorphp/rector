<?php

namespace Rector\Order\Tests\Rector\Class_\OrderFirstLevelClassStatementsRector\Fixture;

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

namespace Rector\Order\Tests\Rector\Class_\OrderFirstLevelClassStatementsRector\Fixture;

final class SameNames
{
    protected $name;
    protected $anotherName;
    public function name()
    {
    }
}

?>
