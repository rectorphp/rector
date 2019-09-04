<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class StaticTypesInClassResolver
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeTypeResolver $nodeTypeResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param string[] $types
     * @return ObjectType[]
     */
    public function collectStaticCallTypeInClass(Class_ $node, array $types): array
    {
        $staticTypesInClass = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node->stmts, function (Node $node) use (
            $types,
            &$staticTypesInClass
        ) {
            if (! $node instanceof StaticCall) {
                return null;
            }

            foreach ($types as $type) {
                if ($this->nodeTypeResolver->isObjectType($node->class, $type)) {
                    $staticTypesInClass[] = new FullyQualifiedObjectType($type);
                }
            }

            return null;
        });

        return $staticTypesInClass;
    }
}
