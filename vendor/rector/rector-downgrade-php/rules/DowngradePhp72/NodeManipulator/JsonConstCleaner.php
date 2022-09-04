<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class JsonConstCleaner
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param string[] $constants
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr|null
     */
    public function clean($node, array $constants)
    {
        if ($node instanceof ConstFetch) {
            return $this->cleanByConstFetch($node, $constants);
        }
        return $this->cleanByBitwiseOr($node, $constants);
    }
    /**
     * @param string[] $constants
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node
     */
    private function hasDefinedCheck($node, array $constants) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($node, function (Node $subNode) use($constants) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode, 'defined')) {
                return \false;
            }
            $args = $subNode->getArgs();
            if (!isset($args[0])) {
                return \false;
            }
            if (!$args[0]->value instanceof String_) {
                return \false;
            }
            return \in_array($args[0]->value->value, $constants, \true);
        });
    }
    /**
     * @param string[] $constants
     */
    private function cleanByConstFetch(ConstFetch $constFetch, array $constants) : ?LNumber
    {
        if (!$this->nodeNameResolver->isNames($constFetch, $constants)) {
            return null;
        }
        $parentNode = $constFetch->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof BitwiseOr) {
            return null;
        }
        if ($this->hasDefinedCheck($constFetch, $constants)) {
            return null;
        }
        return new LNumber(0);
    }
    /**
     * @param string[] $constants
     * @return null|\PhpParser\Node\Expr|\PhpParser\Node\Scalar\LNumber
     */
    private function cleanByBitwiseOr(BitwiseOr $bitwiseOr, array $constants)
    {
        $isLeftTransformed = $this->isTransformed($bitwiseOr->left, $constants);
        $isRightTransformed = $this->isTransformed($bitwiseOr->right, $constants);
        if (!$isLeftTransformed && !$isRightTransformed) {
            return null;
        }
        if ($this->hasDefinedCheck($bitwiseOr, $constants)) {
            return null;
        }
        if (!$isLeftTransformed) {
            return $bitwiseOr->left;
        }
        if (!$isRightTransformed) {
            return $bitwiseOr->right;
        }
        return new LNumber(0);
    }
    /**
     * @param string[] $constants
     */
    private function isTransformed(Expr $expr, array $constants) : bool
    {
        return $expr instanceof ConstFetch && $this->nodeNameResolver->isNames($expr, $constants);
    }
}
