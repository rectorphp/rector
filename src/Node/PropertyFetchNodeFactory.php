<?php declare(strict_types=1);

namespace Rector\Node;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;

final class PropertyFetchNodeFactory
{
    /**
     * Creates "$variable->property"
     */
    public function createPropertyFetch(string $variable, string $property): PropertyFetch
    {
        $variableNode = new Variable($variable);

        return new PropertyFetch($variableNode, $property);
    }

    /**
     * Creates "$this->propertyName"
     */
    public function createLocalPropertyFetch(string $propertyName): PropertyFetch
    {
        $localVariable = new Variable('this', [
            'name' => $propertyName,
        ]);

        return new PropertyFetch($localVariable, $propertyName);
    }

    /**
     * Creates:
     * - $variable->property['key'];
     */
    public function createVariablePropertyArrayFetch(
        Expr $exprNode,
        string $propertyName,
        String_ $keyNode
    ): ArrayDimFetch {
        return new ArrayDimFetch(
            new PropertyFetch($exprNode, new Identifier($propertyName)),
            $keyNode
        );
    }
}
