<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return isset(some_function(), $a, $b, some_function(), $c, some_function());
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
        return some_function() !== null && isset($a, $b) && some_function() !== null && isset($c) && some_function() !== null;
    }
}

?>
