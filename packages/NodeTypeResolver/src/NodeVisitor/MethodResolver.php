<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * Add attribute 'methodName' with current class name.
 */
final class MethodResolver extends NodeVisitorAbstract
{
    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->methodName = null;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->methodName = $node->name->toString();
        }

        if ($this->methodName === null) {
            return;
        }

        $node->setAttribute(Attribute::METHOD_NAME, $this->methodName);
    }
}
