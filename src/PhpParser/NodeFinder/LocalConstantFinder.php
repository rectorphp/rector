<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function match(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : ?\PhpParser\Node\Const_
    {
        $class = $this->betterNodeFinder->findParentType($classConstFetch, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $constantClassType = $this->nodeTypeResolver->getType($classConstFetch->class);
        if (!$constantClassType instanceof \PHPStan\Type\TypeWithClassName) {
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
    private function findConstantByName(\PhpParser\Node\Stmt\Class_ $class, string $constatName) : ?\PhpParser\Node\Const_
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
