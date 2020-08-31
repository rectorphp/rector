<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class FunctionLikeManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        PropertyFetchManipulator $propertyFetchManipulator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
    }

    /**
     * @return string[]
     */
    public function getReturnedLocalPropertyNames(FunctionLike $functionLike): array
    {
        // process only class methods
        if ($functionLike instanceof Function_) {
            return [];
        }

        $returnedLocalPropertyNames = [];
        $this->callableNodeTraverser->traverseNodesWithCallable($functionLike, function (Node $node) use (
            &$returnedLocalPropertyNames
        ): ?void {
            if (! $node instanceof Return_ || $node->expr === null) {
                return null;
            }

            if (! $this->propertyFetchManipulator->isLocalPropertyFetch($node->expr)) {
                return null;
            }

            $propertyName = $this->nodeNameResolver->getName($node->expr);
            if ($propertyName === null) {
                return null;
            }

            $returnedLocalPropertyNames[] = $propertyName;
        });

        return $returnedLocalPropertyNames;
    }
}
