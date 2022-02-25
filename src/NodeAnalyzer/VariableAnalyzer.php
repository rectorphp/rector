<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VariableAnalyzer
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeComparator $nodeComparator
    ) {
    }

    public function isStaticOrGlobal(Variable $variable): bool
    {
        if ($this->isParentStaticOrGlobal($variable)) {
            return true;
        }

        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (Node $n) use (
            $variable
        ): bool {
            if (! in_array($n::class, [Static_::class, Global_::class], true)) {
                return false;
            }

            /**
             * @var Static_|Global_ $n
             * @var StaticVar[]|Variable[] $vars
             */
            $vars = $n->vars;
            foreach ($vars as $var) {
                $staticVarVariable = $var instanceof StaticVar
                    ? $var->var
                    : $var;

                if ($this->nodeComparator->areNodesEqual($staticVarVariable, $variable)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function isUsedByReference(Variable $variable): bool
    {
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (Node $subNode) use (
            $variable
        ): bool {
            if (! $subNode instanceof Variable) {
                return false;
            }

            if (! $this->nodeComparator->areNodesEqual($subNode, $variable)) {
                return false;
            }

            $parent = $subNode->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parent instanceof ClosureUse) {
                return false;
            }

            return $parent->byRef;
        });
    }

    private function isParentStaticOrGlobal(Variable $variable): bool
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);

        if (! $parentNode instanceof Node) {
            return false;
        }

        if ($parentNode instanceof Global_) {
            return true;
        }

        if (! $parentNode instanceof StaticVar) {
            return false;
        }

        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        return $parentParentNode instanceof Static_;
    }
}
