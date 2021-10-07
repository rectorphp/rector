<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class LocalConstantFinder
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
    public function match(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : ?\PhpParser\Node\Const_
    {
        $class = $classConstFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
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
