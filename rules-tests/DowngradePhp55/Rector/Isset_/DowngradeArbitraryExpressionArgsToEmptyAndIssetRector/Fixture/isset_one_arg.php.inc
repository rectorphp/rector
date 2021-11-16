<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return isset(some_function()) && isset($var);
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
        return some_function() !== null && isset($var);
    }
}

?>
