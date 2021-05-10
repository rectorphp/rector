<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class StaticTypesInClassResolver
{
    public function __construct(
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeTypeResolver $nodeTypeResolver
    ) {
    }

    /**
     * @param ObjectType[] $objectTypes
     * @return ObjectType[]
     */
    public function collectStaticCallTypeInClass(Class_ $class, array $objectTypes): array
    {
        $staticTypesInClass = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $class) use (
            $objectTypes,
            &$staticTypesInClass
        ) {
            if (! $class instanceof StaticCall) {
                return null;
            }

            foreach ($objectTypes as $objectType) {
                if ($this->nodeTypeResolver->isObjectType($class->class, $objectType)) {
                    $staticTypesInClass[] = $objectType;
                }
            }

            return null;
        });

        return $staticTypesInClass;
    }
}
