<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class ControllerClassMethodAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isInControllerActionMethod(Variable $variable): bool
    {
        $class = $this->betterNodeFinder->findParentType($variable, Class_::class);
        if (! $class instanceof Class_) {
            return false;
        }

        $className = $this->nodeNameResolver->getName($class);
        if (! is_string($className)) {
            return false;
        }

        if (! \str_ends_with($className, 'Controller')) {
            return false;
        }

        $classMethod = $this->betterNodeFinder->findParentType($variable, ClassMethod::class);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        // is probably in controller action
        return $classMethod->isPublic();
    }
}
