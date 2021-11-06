<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ControllerClassMethodAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function isInControllerActionMethod(Variable $variable): bool
    {
        /** @var string|null $className */
        $className = $variable->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
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
