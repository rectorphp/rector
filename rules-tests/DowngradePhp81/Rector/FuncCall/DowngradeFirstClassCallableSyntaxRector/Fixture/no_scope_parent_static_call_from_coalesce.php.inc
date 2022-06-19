<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class Container
{
    protected function getEnv(string $name): mixed
    {
        $this->getEnv ??= $this->getEnv(...);
        return null;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\Fixture;

class Container
{
    protected function getEnv(string $name): mixed
    {
        $this->getEnv ??= \Closure::fromCallable([$this, 'getEnv']);
        return null;
    }
}

?>
