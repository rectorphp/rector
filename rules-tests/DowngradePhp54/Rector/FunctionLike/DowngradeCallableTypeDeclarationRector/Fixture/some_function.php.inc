<?php

namespace Rector\Tests\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector\Fixture;

function run(callable $callback)
{
    return $callback();
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector\Fixture;

/**
 * @param callable $callback
 */
function run($callback)
{
    return $callback();
}

?>
