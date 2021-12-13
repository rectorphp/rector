<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

final class ArrowFunctionReturnMixed
{
    public function run()
    {
        $value = fn () : mixed => rand(0, 1) ? '1' : 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

final class ArrowFunctionReturnMixed
{
    public function run()
    {
        $value = fn () => rand(0, 1) ? '1' : 1;
    }
}

?>
