<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class StaticCallPresenceAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeTypeResolver $nodeTypeResolver
    ) {
    }

    public function hasMethodStaticCallOnType(ClassMethod $classMethod, ObjectType $objectType): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            (array) $classMethod->stmts,
            function (Node $node) use ($objectType): bool {
                if (! $node instanceof StaticCall) {
                    return false;
                }

                return $this->nodeTypeResolver->isObjectType($node->class, $objectType);
            }
        );
    }

    public function hasClassAnyMethodWithStaticCallOnType(Class_ $class, ObjectType $objectType): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            // handled else where
            if ((string) $classMethod->name === MethodName::CONSTRUCT) {
                continue;
            }

            if ($this->hasMethodStaticCallOnType($classMethod, $objectType)) {
                return true;
            }
        }

        return false;
    }
}
