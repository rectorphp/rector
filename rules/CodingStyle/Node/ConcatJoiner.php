<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\CodingStyle\ValueObject\ConcatStringAndPlaceholders;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ConcatJoiner
{
    /**
     * @var string
     */
    private $content = '';
    /**
     * @var Expr[]
     */
    private $placeholderNodes = [];
    /**
     * Joins all String_ nodes to string.
     * Returns that string + array of non-string nodes that were replaced by hash placeholders
     */
    public function joinToStringAndPlaceholderNodes(\PhpParser\Node\Expr\BinaryOp\Concat $concat) : \Rector\CodingStyle\ValueObject\ConcatStringAndPlaceholders
    {
        $parentNode = $concat->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $this->reset();
        }
        $this->processConcatSide($concat->left);
        $this->processConcatSide($concat->right);
        return new \Rector\CodingStyle\ValueObject\ConcatStringAndPlaceholders($this->content, $this->placeholderNodes);
    }
    private function reset() : void
    {
        $this->content = '';
        $this->placeholderNodes = [];
    }
    private function processConcatSide(\PhpParser\Node\Expr $expr) : void
    {
        if ($expr instanceof \PhpParser\Node\Scalar\String_) {
            $this->content .= $expr->value;
        } elseif ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $this->joinToStringAndPlaceholderNodes($expr);
        } else {
            $objectHash = '____' . \spl_object_hash($expr) . '____';
            $this->placeholderNodes[$objectHash] = $expr;
            $this->content .= $objectHash;
        }
    }
}
