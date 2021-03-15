<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;

final class KeepBetterType
{
    /**
     * @param MethodCall|StaticCall $someObject
     */
    public function someFunction(object $someObject)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;

final class KeepBetterType
{
    /**
     * @param MethodCall|StaticCall $someObject
     */
    public function someFunction($someObject)
    {
    }
}

?>
