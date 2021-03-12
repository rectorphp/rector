<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RespectVendorReturnType;

final class RespectVendorReturnType2 extends RespectVendorReturnType
{
    public function run()
    {
       return true;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\RespectVendorReturnType;

final class RespectVendorReturnType2 extends RespectVendorReturnType
{
    public function run(): bool
    {
       return true;
    }
}

?>
