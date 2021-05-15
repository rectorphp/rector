<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class GetSubscribedEventsClassMethodAnalyzer
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classLike = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectType($classLike, new \PHPStan\Type\ObjectType('Kdyby\\Events\\Subscriber'))) {
            return \false;
        }
        return $this->nodeNameResolver->isName($classMethod, 'getSubscribedEvents');
    }
}
