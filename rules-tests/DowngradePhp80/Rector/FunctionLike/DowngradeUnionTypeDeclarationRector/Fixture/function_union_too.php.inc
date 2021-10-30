<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use PhpParser\Node\Name;

if (! function_exists('some_helper')) {
    function some_helper(Name|array $item)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use PhpParser\Node\Name;

if (! function_exists('some_helper')) {
    /**
     * @param mixed[]|\PhpParser\Node\Name $item
     */
    function some_helper($item)
    {
    }
}

?>
