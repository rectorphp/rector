<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class NetteClassAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeTypeResolver $nodeTypeResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isInComponent(Node $node) : bool
    {
        $class = $node instanceof Class_ ? $node : $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectType($class, new ObjectType('Nette\\Application\\UI\\Control'))) {
            return \false;
        }
        return !$this->nodeTypeResolver->isObjectType($class, new ObjectType('Nette\\Application\\UI\\Presenter'));
    }
}
