<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class AnonymousFunction
{
    public function return()
    {
        $callable = function (object $someObject) {
            // ...
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\Fixture;

class AnonymousFunction
{
    public function return()
    {
        $callable = function ($someObject) {
            // ...
        };
    }
}

?>
