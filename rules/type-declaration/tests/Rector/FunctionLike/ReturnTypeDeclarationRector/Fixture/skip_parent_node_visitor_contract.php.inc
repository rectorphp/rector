<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\NodeVisitorAbstract;

final class SkipParentNodeVisitorContract extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        /** @var String_ $node */
        return $node;
    }

    public function run()
    {
        return 1;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\NodeVisitorAbstract;

final class SkipParentNodeVisitorContract extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        /** @var String_ $node */
        return $node;
    }

    public function run(): int
    {
        return 1;
    }
}

?>
