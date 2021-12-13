<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class ArrowFunctionParamMixed
{
    public function run()
    {
        $value = fn (mixed $param): bool => true;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class ArrowFunctionParamMixed
{
    public function run()
    {
        $value = fn ($param): bool => true;
    }
}

?>
