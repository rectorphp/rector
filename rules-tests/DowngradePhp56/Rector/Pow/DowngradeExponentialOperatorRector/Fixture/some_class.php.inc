<?php

namespace Rector\Tests\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return 2 ** 3;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return pow(2, 3);
    }
}

?>
