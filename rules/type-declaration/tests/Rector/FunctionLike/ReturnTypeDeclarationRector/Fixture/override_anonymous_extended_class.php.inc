<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\NodeVisitorAbstract;

final class OverrideAnonymousExtendedClass
{
    public function getNodeVisitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
        };
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\NodeVisitorAbstract;

final class OverrideAnonymousExtendedClass
{
    public function getNodeVisitor(): object
    {
        return new class extends NodeVisitorAbstract
        {
        };
    }
}

?>
