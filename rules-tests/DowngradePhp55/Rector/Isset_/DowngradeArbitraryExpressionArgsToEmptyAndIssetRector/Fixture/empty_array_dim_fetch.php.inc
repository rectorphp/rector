<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return empty(some_function()['a']) && empty(['b']);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return empty(some_function()['a']) && !['b'];
    }
}

?>
