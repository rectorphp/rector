<?php

declare(strict_types=1);

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
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    public function match(ClassConstFetch $classConstFetch): ?Const_
    {
        $class = $classConstFetch->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        $constantClassType = $this->nodeTypeResolver->resolve($classConstFetch->class);
        if (! $constantClassType instanceof TypeWithClassName) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($class, $constantClassType->getClassName())) {
            return null;
        }

        $constatName = $this->nodeNameResolver->getName($classConstFetch->name);
        if ($constatName === null) {
            return null;
        }

        return $this->findConstantByName($class, $constatName);
    }

    private function findConstantByName(Class_ $class, string $constatName): ?Const_
    {
        foreach ($class->getConstants() as $classConsts) {
            foreach ($classConsts->consts as $const) {
                if (! $this->nodeNameResolver->isName($const->name, $constatName)) {
                    continue;
                }

                return $const;
            }
        }

        return null;
    }
}
