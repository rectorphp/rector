<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ExternalForVoidInterface;

final class ExternalVoidInterface implements ExternalForVoidInterface
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ExternalForVoidInterface;

final class ExternalVoidInterface implements ExternalForVoidInterface
{
    public function run(): void
    {
    }
}

?>
