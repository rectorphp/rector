<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class SomeClass
{
    public function run(): \Closure
    {
        return self::dump(...);
    }

    public static function dump(...$args): void
    {
        var_dump(...$args);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class SomeClass
{
    public function run(): \Closure
    {
        return \Closure::fromCallable([self::class, 'dump']);
    }

    public static function dump(...$args): void
    {
        var_dump(...$args);
    }
}

?>
