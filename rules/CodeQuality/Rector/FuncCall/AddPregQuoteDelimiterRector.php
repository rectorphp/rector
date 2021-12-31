<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use RectorPrefix20211231\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector\AddPregQuoteDelimiterRectorTest
 */
final class AddPregQuoteDelimiterRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const ALL_MODIFIERS = 'imsxeADSUXJu';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add preg_quote delimiter when missing', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
'#' . preg_quote('name') . '#';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'#' . preg_quote('name', '#') . '#';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'preg_quote')) {
            return null;
        }
        // already completed
        if (isset($node->args[1])) {
            return null;
        }
        $delimiter = $this->determineDelimiter($node);
        if ($delimiter === null) {
            return null;
        }
        $node->args[1] = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_($delimiter));
        return $node;
    }
    private function determineDelimiter(\PhpParser\Node\Expr\FuncCall $funcCall) : ?string
    {
        $concat = $this->getUppermostConcat($funcCall);
        if (!$concat instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            return null;
        }
        $leftMostConcatNode = $concat->left;
        while ($leftMostConcatNode instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $leftMostConcatNode = $leftMostConcatNode->left;
        }
        $rightMostConcatNode = $concat->right;
        while ($rightMostConcatNode instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $rightMostConcatNode = $rightMostConcatNode->right;
        }
        if (!$leftMostConcatNode instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        $possibleLeftDelimiter = \RectorPrefix20211231\Nette\Utils\Strings::substring($leftMostConcatNode->value, 0, 1);
        if (!$rightMostConcatNode instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        $possibleRightDelimiter = \RectorPrefix20211231\Nette\Utils\Strings::substring(\rtrim($rightMostConcatNode->value, self::ALL_MODIFIERS), -1, 1);
        if ($possibleLeftDelimiter === $possibleRightDelimiter) {
            return $possibleLeftDelimiter;
        }
        return null;
    }
    private function getUppermostConcat(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\BinaryOp\Concat
    {
        $upperMostConcat = null;
        $parent = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        while ($parent instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $upperMostConcat = $parent;
            $parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        }
        return $upperMostConcat;
    }
}
