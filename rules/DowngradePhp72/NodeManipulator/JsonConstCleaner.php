<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class JsonConstCleaner
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @param string[] $constants
     */
    public function clean(ConstFetch|BitwiseOr $node, array $constants): ConstFetch|Expr|null
    {
        $defined = (bool) $this->betterNodeFinder->findFirstPreviousOfNode(
            $node,
            function (Node $subNode) use ($constants): bool {
                if (! $subNode instanceof FuncCall) {
                    return false;
                }

                if (! $this->nodeNameResolver->isName($subNode, 'defined')) {
                    return false;
                }

                $args = $subNode->getArgs();
                if (! isset($args[0])) {
                    return false;
                }

                if (! $args[0]->value instanceof String_) {
                    return false;
                }

                return in_array($args[0]->value->value, $constants, true);
            }
        );

        if ($defined) {
            return null;
        }

        if ($node instanceof ConstFetch) {
            return $this->cleanByConstFetch($node, $constants);
        }

        return $this->cleanByBitwiseOr($node, $constants);
    }

    /**
     * @param string[] $constants
     */
    private function cleanByConstFetch(ConstFetch $constFetch, array $constants): ?ConstFetch
    {
        if (! $this->nodeNameResolver->isNames($constFetch, $constants)) {
            return null;
        }

        $parent = $constFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof BitwiseOr) {
            return new ConstFetch(new Name('0'));
        }

        return null;
    }

    /**
     * @param string[] $constants
     */
    private function cleanByBitwiseOr(BitwiseOr $bitwiseOr, array $constants): ?Expr
    {
        $isLeftTransformed = $this->isTransformed($bitwiseOr->left, $constants);
        $isRightTransformed = $this->isTransformed($bitwiseOr->right, $constants);

        if (! $isLeftTransformed && ! $isRightTransformed) {
            return null;
        }

        if (! $isLeftTransformed) {
            return $bitwiseOr->left;
        }

        if (! $isRightTransformed) {
            return $bitwiseOr->right;
        }

        return new ConstFetch(new Name('0'));
    }

    /**
     * @param string[] $constants
     */
    private function isTransformed(Expr $expr, array $constants): bool
    {
        return $expr instanceof ConstFetch && $this->nodeNameResolver->isNames($expr, $constants);
    }
}
