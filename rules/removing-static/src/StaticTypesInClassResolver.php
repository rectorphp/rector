<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class StaticTypesInClassResolver
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param string[] $types
     * @return ObjectType[]
     */
    public function collectStaticCallTypeInClass(Class_ $class, array $types): array
    {
        $staticTypesInClass = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $class) use (
            $types,
            &$staticTypesInClass
        ) {
            if (! $class instanceof StaticCall) {
                return null;
            }

            foreach ($types as $type) {
                if ($this->nodeTypeResolver->isObjectType($class->class, $type)) {
                    $staticTypesInClass[] = new FullyQualifiedObjectType($type);
                }
            }

            return null;
        });

        return $staticTypesInClass;
    }
}
