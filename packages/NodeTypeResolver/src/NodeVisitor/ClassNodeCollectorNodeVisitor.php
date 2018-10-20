<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Application\ClassNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;

final class ClassNodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassNodeCollector
     */
    private $classNodeCollector;

    public function __construct(ClassNodeCollector $classNodeCollector)
    {
        $this->classNodeCollector = $classNodeCollector;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if (! $node instanceof Class_) {
            return;
        }

        if ($node->isAnonymous()) {
            return;
        }

        $className = (string) $node->getAttribute(Attribute::CLASS_NAME);
        $this->classNodeCollector->addClass($className, $node);
    }
}
