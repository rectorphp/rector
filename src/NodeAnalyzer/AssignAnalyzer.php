<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;

final class AssignAnalyzer
{
    /**
     * Checks "$this->specificNameProperty =" and it's type
     *
     * @param string[] $methodsNames
     */
    public function isAssignTypeAndProperty(Node $node, string $expectedType, string $expectedPropertyName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }

        return $this->isVariableTypeAndPropetyName($node, $expectedType, $expectedPropertyName);
    }

    /**
     * Checks "$variable[] = '...';"
     */
    public function isArrayAssignTypeAndProperty(Node $node, string $expectedType, string $expectedPropertyName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->var instanceof ArrayDimFetch) {
            return false;
        }

        /** @var ArrayDimFetch $arrayDimFetchNode */
        $arrayDimFetchNode = $node->var;

        return $this->isVariableTypeAndPropetyName(
            $arrayDimFetchNode,
            $expectedType,
            $expectedPropertyName
        );
    }

    /**
     * Checks "$specificNameVariable = " and its type
     *
     * @param Assign|ArrayDimFetch $assignOrArrayDimFetchNode
     */
    private function isVariableTypeAndPropetyName(
        Node $assignOrArrayDimFetchNode,
        string $expectedType,
        string $expectedPropertyName
    ): bool {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignOrArrayDimFetchNode->var;

        $variableTypes = $propertyFetchNode->var->getAttribute(Attribute::TYPES);
        if (! in_array($expectedType, $variableTypes, true)) {
            return false;
        }

        $propertyName = $propertyFetchNode->name->name;

        return $propertyName === $expectedPropertyName;
    }
}
