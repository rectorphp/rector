<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
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
     * @var string|null
     */
    private $className;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->className = null;
    }

    public function enterNode(Node $node): void
    {
        // detect only first "class" to prevent anonymous classes interference
        if ($this->className === null && $node instanceof Node\Stmt\Class_) {
            $this->className = $node->namespacedName->toString();
        }

        if ($this->className) {
            $node->setAttribute(self::CLASS_ATTRIBUTE, $this->className);
        }
    }
}
