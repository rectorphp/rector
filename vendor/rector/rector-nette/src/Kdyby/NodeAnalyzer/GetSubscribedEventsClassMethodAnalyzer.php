<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class GetSubscribedEventsClassMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function detect(ClassMethod $classMethod) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectType($classLike, new ObjectType('Kdyby\\Events\\Subscriber'))) {
            return \false;
        }
        return $this->nodeNameResolver->isName($classMethod, 'getSubscribedEvents');
    }
}
