<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
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
        $name = (string) $node->getAttribute(Attribute::CLASS_NAME);

        if ($node instanceof Class_ && $node->isAnonymous() === false) {
            $this->classLikeNodeCollector->addClass($name, $node);
        }

        if ($node instanceof Interface_) {
            $this->classLikeNodeCollector->addInterface($name, $node);
        }

        if ($node instanceof Trait_) {
            $this->classLikeNodeCollector->addTrait($name, $node);
        }
    }
}
