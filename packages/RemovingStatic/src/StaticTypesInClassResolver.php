<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class StaticTypesInClassResolver
{
    /**
     * @var string[]
     */
    private $staticTypesInClass = [];

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
     * @return string[]
     */
    public function collectStaticCallTypeInClass(Class_ $node, array $types): array
    {
        $this->staticTypesInClass = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node->stmts, function (Node $node) use ($types) {
            if (! $node instanceof StaticCall) {
                return null;
            }

            foreach ($types as $type) {
                if ($this->nodeTypeResolver->isType($node, $type)) {
                    $this->staticTypesInClass[] = $type;
                }
            }

            return null;
        });

        return $this->staticTypesInClass;
    }
}
