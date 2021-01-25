<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use Rector\TypeDeclaration\Matcher\PropertyAssignMatcher;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

abstract class AbstractAssignDetector
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    protected $simpleCallableNodeTraverser;

    /**
     * @var PropertyAssignMatcher
     */
    private $propertyAssignMatcher;

    /**
     * @required
     */
    public function autowireAbstractAssignDetector(
        PropertyAssignMatcher $propertyAssignMatcher,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ): void {
        $this->propertyAssignMatcher = $propertyAssignMatcher;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    protected function matchAssignExprToPropertyName(Node $node, string $propertyName): ?Expr
    {
        if (! $node instanceof Assign) {
            return null;
        }

        return $this->propertyAssignMatcher->matchPropertyAssignExpr($node, $propertyName);
    }
}
