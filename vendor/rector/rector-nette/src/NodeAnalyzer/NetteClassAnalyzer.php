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
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isInComponent(\PhpParser\Node $node) : bool
    {
        $class = $node instanceof \PhpParser\Node\Stmt\Class_ ? $node : $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectType($class, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'))) {
            return \false;
        }
        return !$this->nodeTypeResolver->isObjectType($class, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Presenter'));
    }
}
