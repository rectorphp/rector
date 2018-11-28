<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;

final class ClassLikeNodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    public function __construct(ClassLikeNodeCollector $classLikeNodeCollector)
    {
        $this->classLikeNodeCollector = $classLikeNodeCollector;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_ && $node->isAnonymous() === false) {
            $className = (string) $node->getAttribute(Attribute::CLASS_NAME);
            $this->classLikeNodeCollector->addClass($className, $node);
        }

        if ($node instanceof Interface_) {
            $interfaceName = (string) $node->getAttribute(Attribute::CLASS_NAME);
            $this->classLikeNodeCollector->addInterface($interfaceName, $node);
        }
    }
}
