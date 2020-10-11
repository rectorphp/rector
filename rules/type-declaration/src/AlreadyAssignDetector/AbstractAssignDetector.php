<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\TypeDeclaration\Matcher\PropertyAssignMatcher;

abstract class AbstractAssignDetector
{
    /**
     * @var CallableNodeTraverser
     */
    protected $callableNodeTraverser;

    /**
     * @var PropertyAssignMatcher
     */
    private $propertyAssignMatcher;

    /**
     * @required
     */
    public function autowireAbstractAssignDetector(
        PropertyAssignMatcher $propertyAssignMatcher,
        CallableNodeTraverser $callableNodeTraverser
    ): void {
        $this->propertyAssignMatcher = $propertyAssignMatcher;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    protected function matchAssignExprToPropertyName(Node $node, string $propertyName): ?Expr
    {
        if (! $node instanceof Assign) {
            return null;
        }

        return $this->propertyAssignMatcher->matchPropertyAssignExpr($node, $propertyName);
    }
}
