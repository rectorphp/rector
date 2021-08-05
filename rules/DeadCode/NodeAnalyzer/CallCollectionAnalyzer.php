<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CallCollectionAnalyzer
{
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param StaticCall[]|MethodCall[] $calls
     */
    public function isExists(array $calls, string $classMethodName, ?string $className): bool
    {
        foreach ($calls as $call) {
            $callerRoot = $call instanceof StaticCall ? $call->class : $call->var;
            $callerType = $this->nodeTypeResolver->resolve($callerRoot);

            if (! $callerType instanceof TypeWithClassName) {
                continue;
            }

            if ($callerType->getClassName() !== $className) {
                continue;
            }

            // the method is used
            if ($this->nodeNameResolver->isName($call->name, $classMethodName)) {
                return true;
            }
        }

        return false;
    }
}
