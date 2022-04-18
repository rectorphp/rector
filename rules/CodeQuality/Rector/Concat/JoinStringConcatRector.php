<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Concat;

use RectorPrefix20220418\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Concat\JoinStringConcatRector\JoinStringConcatRectorTest
 */
final class JoinStringConcatRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var int
     */
    private const LINE_BREAK_POINT = 100;
    /**
     * @var bool
     */
    private $nodeReplacementIsRestricted = \false;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Joins concat of 2 strings, unless the length is too long', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi Tom';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Concat::class];
    }
    /**
     * @param Concat $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->nodeReplacementIsRestricted = \false;
        if (!$this->isTopMostConcatNode($node)) {
            return null;
        }
        $joinedNode = $this->joinConcatIfStrings($node);
        if (!$joinedNode instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        if ($this->nodeReplacementIsRestricted) {
            return null;
        }
        return $joinedNode;
    }
    private function isTopMostConcatNode(\PhpParser\Node\Expr\BinaryOp\Concat $concat) : bool
    {
        $parent = $concat->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        return !$parent instanceof \PhpParser\Node\Expr\BinaryOp\Concat;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Scalar\String_
     */
    private function joinConcatIfStrings(\PhpParser\Node\Expr\BinaryOp\Concat $node)
    {
        $concat = clone $node;
        if ($concat->left instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $concat->left = $this->joinConcatIfStrings($concat->left);
        }
        if ($concat->right instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $concat->right = $this->joinConcatIfStrings($concat->right);
        }
        if (!$concat->left instanceof \PhpParser\Node\Scalar\String_) {
            return $node;
        }
        if (!$concat->right instanceof \PhpParser\Node\Scalar\String_) {
            return $node;
        }
        $leftValue = $concat->left->value;
        $rightValue = $concat->right->value;
        if ($leftValue === "\n") {
            $this->nodeReplacementIsRestricted = \true;
            return $node;
        }
        if ($rightValue === "\n") {
            $this->nodeReplacementIsRestricted = \true;
            return $node;
        }
        $resultString = new \PhpParser\Node\Scalar\String_($leftValue . $rightValue);
        if (\RectorPrefix20220418\Nette\Utils\Strings::length($resultString->value) >= self::LINE_BREAK_POINT) {
            $this->nodeReplacementIsRestricted = \true;
            return $node;
        }
        return $resultString;
    }
}
