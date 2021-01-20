<?php
declare(strict_types=1);

namespace Rector\NetteCodeQuality\NodeAdding;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FunctionLikeFirstLevelStatementResolver
{
    /**
     * @var ParentScopeFinder
     */
    private $parentScopeFinder;

    public function __construct(ParentScopeFinder $parentScopeFinder)
    {
        $this->parentScopeFinder = $parentScopeFinder;
    }

    public function resolveFirstLevelStatement(Node $node): Node
    {
        $multiplierClosure = $this->matchMultiplierClosure($node);
        /** @var ClassMethod|Closure|null $functionLike */
        $functionLike = $multiplierClosure ?? $this->parentScopeFinder->find($node);

        if ($functionLike === null) {
            throw new ShouldNotHappenException();
        }

        $currentStatement = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if (! $currentStatement instanceof Node) {
            throw new ShouldNotHappenException();
        }

        while (! in_array($currentStatement, (array) $functionLike->stmts, true)) {
            $parent = $currentStatement->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parent instanceof Node) {
                throw new ShouldNotHappenException();
            }

            $currentStatement = $parent->getAttribute(AttributeKey::CURRENT_STATEMENT);
        }

        return $currentStatement;
    }

    /**
     * Form might be costructured inside private closure for multiplier
     * @see https://doc.nette.org/en/3.0/multiplier
     */
    private function matchMultiplierClosure(Node $node): ?Closure
    {
        $closure = $node->getAttribute(AttributeKey::CLOSURE_NODE);
        if (! $closure instanceof Closure) {
            return null;
        }

        $parent = $closure->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Arg) {
            return null;
        }

        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof New_) {
            return null;
        }

        return $closure;
    }
}
