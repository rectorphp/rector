<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class IncludeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Stmt|null
     */
    private $previousStmt;

    public function beforeTraverse(array $nodes): ?array
    {
        $this->previousStmt = null;
        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        $this->previousStmt = $node;
    }
}
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class IncludeNodeVisitor extends NodeVisitorAbstract
{
    private ?\PhpParser\Node $previousStmt = null;

    public function beforeTraverse(array $nodes): ?array
    {
        $this->previousStmt = null;
        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        $this->previousStmt = $node;
    }
}
