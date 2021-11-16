<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    /**
     * @return bool
     */
    public function run()
    {
        return empty(!trim(''));
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    /**
     * @return bool
     */
    public function run()
    {
        return !!trim('');
    }
}

?>
