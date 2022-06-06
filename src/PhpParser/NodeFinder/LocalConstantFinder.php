<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PhpParser\NodeFinder;

use RectorPrefix20220606\PhpParser\Node\Const_;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
final class LocalConstantFinder
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
    public function match(ClassConstFetch $classConstFetch) : ?Const_
    {
        $class = $this->betterNodeFinder->findParentType($classConstFetch, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $constantClassType = $this->nodeTypeResolver->getType($classConstFetch->class);
        if (!$constantClassType instanceof TypeWithClassName) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($class, $constantClassType->getClassName())) {
            return null;
        }
        $constatName = $this->nodeNameResolver->getName($classConstFetch->name);
        if ($constatName === null) {
            return null;
        }
        return $this->findConstantByName($class, $constatName);
    }
    private function findConstantByName(Class_ $class, string $constatName) : ?Const_
    {
        foreach ($class->getConstants() as $classConsts) {
            foreach ($classConsts->consts as $const) {
                if (!$this->nodeNameResolver->isName($const->name, $constatName)) {
                    continue;
                }
                return $const;
            }
        }
        return null;
    }
}
