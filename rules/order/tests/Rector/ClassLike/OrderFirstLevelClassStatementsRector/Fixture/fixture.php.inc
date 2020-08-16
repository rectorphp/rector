<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Fixture;

use Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Source\TraitName;
use Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Source\TraitName2;

final class SomeClass
{
    public function functionName()
    {
    }
    protected $propertyName;
    use TraitName;
    private const CONST_NAME = 'constant_value';
    use TraitName2;
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Fixture;

use Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Source\TraitName;
use Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\Source\TraitName2;

final class SomeClass
{
    use TraitName;
    use TraitName2;
    private const CONST_NAME = 'constant_value';
    protected $propertyName;
    public function functionName()
    {
    }
}

?>
