<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
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

    public function matchNameOnFormOrControlVariable(Node $node): ?string
    {
        return $this->matchNameOnVariableTypes($node, ['Nette\Application\UI\Form']);
    }

    public function matchNameOnControlVariable(Node $node): ?string
    {
        return $this->matchNameOnVariableTypes($node, ['Nette\Application\UI\Control']);
    }

    public function matchName(Node $node): ?string
    {
        if (! $node instanceof ArrayDimFetch) {
            return null;
        }

        if (! $this->isVariableTypes($node->var, ['Nette\ComponentModel\IContainer'])) {
            return null;
        }

        if (! $node->dim instanceof String_) {
            return null;
        }

        return $node->dim->value;
    }

    /**
     * @param string[] $types
     */
    private function matchNameOnVariableTypes(Node $node, array $types): ?string
    {
        $matchedName = $this->matchName($node);
        if ($matchedName === null) {
            return null;
        }

        /** @var Assign $node */
        if (! $this->isVariableTypes($node->var, $types)) {
            return null;
        }

        return $matchedName;
    }

    /**
     * @param string[] $types
     */
    private function isVariableTypes(Node $node, array $types): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        foreach ($types as $type) {
            if ($this->nodeTypeResolver->isObjectTypeOrNullableObjectType($node, $type)) {
                return true;
            }
        }

        return false;
    }
}
