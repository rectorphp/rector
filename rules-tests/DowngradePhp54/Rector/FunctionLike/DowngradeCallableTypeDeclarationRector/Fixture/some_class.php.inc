<?php

namespace Rector\Tests\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector\Fixture;

final class SomeClass
{
    public function run(callable $callback)
    {
        return $callback();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector\Fixture;

final class SomeClass
{
    /**
     * @param callable $callback
     */
    public function run($callback)
    {
        return $callback();
    }
}

?>
