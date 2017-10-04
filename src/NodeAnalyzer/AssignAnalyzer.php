<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;

final class AssignAnalyzer
{
    /**
     * @param string[] $methodsNames
     */
    public function isAssignTypeAndProperty(Node $node, string $expectedType, string $exptectedPropertyName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }

        $variableType = $node->var->var->getAttribute(Attribute::TYPE);
        if ($variableType !== $expectedType) {
            return false;
        }

        $propertyName = $node->var->name->name;

        return $propertyName === $exptectedPropertyName;
    }
}
