<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

/**
 * Add attribute 'class' with current class name.
 */
final class ClassResolver extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const CLASS_ATTRIBUTE = 'class';

    /**
     * @var string
     */
    private const CLASS_NODE_ATTRIBUTE = 'class_node';

    /**
     * @var Class_|null
     */
    private $classNode;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->classNode = null;
    }

    public function enterNode(Node $node): void
    {
        // detect only first "class" to prevent anonymous classes interference
        if ($this->classNode === null && $node instanceof Class_) {
            $this->classNode = $node;
        }

        if ($this->classNode) {
            $node->setAttribute(self::CLASS_NODE_ATTRIBUTE, $this->classNode);
            $node->setAttribute(self::CLASS_ATTRIBUTE, $this->classNode->namespacedName->toString());
        }
    }
}
