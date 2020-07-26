<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ControlDimFetchAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function matchName(Node $node): ?string
    {
        if (! $node instanceof ArrayDimFetch) {
            return null;
        }

        if (! $this->isContainerVariable($node->var)) {
            return null;
        }

        if (! $node->dim instanceof String_) {
            return null;
        }

        return $node->dim->value;
    }

    private function isContainerVariable(Node $node): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        return $this->nodeTypeResolver->isObjectTypeOrNullableObjectType($node, 'Nette\ComponentModel\IContainer');
    }
}
