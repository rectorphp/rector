<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class FunctionLikeManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    public function __construct(
        NameResolver $nameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        PropertyFetchManipulator $propertyFetchManipulator
    ) {
        $this->nameResolver = $nameResolver;
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
        ) {
            if (! $node instanceof Return_ || $node->expr === null) {
                return null;
            }

            if (! $this->propertyFetchManipulator->isLocalProperty($node->expr)) {
                return null;
            }

            $propertyName = $this->nameResolver->getName($node->expr);
            if ($propertyName === null) {
                return null;
            }

            $returnedLocalPropertyNames[] = $propertyName;
        });

        return $returnedLocalPropertyNames;
    }
}
