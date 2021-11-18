<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class SomeClass
{
    public function run(): \Closure
    {
        return $this->dump(...);
    }

    public function dump(...$args): void
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
        return \Closure::fromCallable([$this, 'dump']);
    }

    public function dump(...$args): void
    {
        var_dump(...$args);
    }
}

?>
