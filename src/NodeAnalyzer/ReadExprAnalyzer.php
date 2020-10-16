<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\NodeFinder\NodeUsageFinder;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ReadExprAnalyzer
{
    /**
     * @var ParentScopeFinder
     */
    private $parentScopeFinder;

    /**
     * @var NodeUsageFinder
     */
    private $nodeUsageFinder;

    public function __construct(ParentScopeFinder $parentScopeFinder, NodeUsageFinder $nodeUsageFinder)
    {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
    }

    /**
     * Is the value read or used for read purpose (at least, not only)
     */
    public function isExprRead(Expr $expr): bool
    {
        if ($expr instanceof Variable) {
            $parentScope = $this->parentScopeFinder->find($expr);
            if ($parentScope === null) {
                return false;
            }

            $variableUsages = $this->nodeUsageFinder->findVariableUsages((array) $parentScope->stmts, $expr);
            foreach ($variableUsages as $variableUsage) {
                if ($this->isCurrentContextRead($variableUsage)) {
                    return true;
                }
            }

            return false;
        }

        throw new NotImplementedYetException(get_class($expr));
    }

    private function isCurrentContextRead(Expr $expr): bool
    {
        $parent = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_) {
            return true;
        }

        throw new NotImplementedYetException();
    }
}
