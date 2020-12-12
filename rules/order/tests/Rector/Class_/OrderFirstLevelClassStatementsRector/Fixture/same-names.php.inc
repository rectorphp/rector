<?php

namespace Rector\Order\Tests\Rector\Class_\OrderFirstLevelClassStatementsRector\Fixture;

final class Same-names
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

final class Same-names
{
    protected $name;
    protected $anotherName;
    public function name()
    {
    }
}

?>
