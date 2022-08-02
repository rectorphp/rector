<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use RectorPrefix202208\Nette\Utils\Strings;
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
final class AddPregQuoteDelimiterRector extends AbstractRector
{
    /**
     * @var string
     * @see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const ALL_MODIFIERS = 'imsxeADSUXJu';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add preg_quote delimiter when missing', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
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
        $node->args[1] = new Arg(new String_($delimiter));
        return $node;
    }
    private function determineDelimiter(FuncCall $funcCall) : ?string
    {
        $concat = $this->getUppermostConcat($funcCall);
        if (!$concat instanceof Concat) {
            return null;
        }
        $leftMostConcatNode = $concat->left;
        while ($leftMostConcatNode instanceof Concat) {
            $leftMostConcatNode = $leftMostConcatNode->left;
        }
        $rightMostConcatNode = $concat->right;
        while ($rightMostConcatNode instanceof Concat) {
            $rightMostConcatNode = $rightMostConcatNode->right;
        }
        if (!$leftMostConcatNode instanceof String_) {
            return null;
        }
        $possibleLeftDelimiter = Strings::substring($leftMostConcatNode->value, 0, 1);
        if (!$rightMostConcatNode instanceof String_) {
            return null;
        }
        $possibleRightDelimiter = Strings::substring(\rtrim($rightMostConcatNode->value, self::ALL_MODIFIERS), -1, 1);
        if ($possibleLeftDelimiter === $possibleRightDelimiter) {
            return $possibleLeftDelimiter;
        }
        return null;
    }
    private function getUppermostConcat(FuncCall $funcCall) : ?Concat
    {
        $upperMostConcat = null;
        $parent = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        while ($parent instanceof Concat) {
            $upperMostConcat = $parent;
            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }
        return $upperMostConcat;
    }
}
