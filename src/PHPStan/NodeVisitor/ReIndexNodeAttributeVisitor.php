<?php

declare (strict_types=1);
namespace Rector\PHPStan\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Application\NodeAttributeReIndexer;
final class ReIndexNodeAttributeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node) : ?Node
    {
        return NodeAttributeReIndexer::reIndexNodeAttributes($node);
    }
}
