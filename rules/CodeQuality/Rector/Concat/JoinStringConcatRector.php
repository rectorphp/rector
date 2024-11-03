<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Concat;

use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\Util\StringUtils;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Concat\JoinStringConcatRector\JoinStringConcatRectorTest
 */
final class JoinStringConcatRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_BREAK_POINT = 100;
    /**
     * @var string
     * @see https://regex101.com/r/VaXM1t/1
     * @see https://stackoverflow.com/questions/4147646/determine-if-utf-8-text-is-all-ascii
     */
    private const ASCII_REGEX = '#[^\\x00-\\x7F]#';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Joins concat of 2 strings, unless the length is too long', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Concat::class];
    }
    /**
     * @param Concat $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->left instanceof String_) {
            return null;
        }
        if (!$node->right instanceof String_) {
            return null;
        }
        $leftStartLine = $node->left->getStartLine();
        $rightStartLine = $node->right->getStartLine();
        if ($leftStartLine > 0 && $rightStartLine > 0 && $rightStartLine > $leftStartLine) {
            return null;
        }
        return $this->joinConcatIfStrings($node->left, $node->right);
    }
    private function joinConcatIfStrings(String_ $leftString, String_ $rightString) : ?String_
    {
        $leftValue = $leftString->value;
        $rightValue = $rightString->value;
        if (\strpos($leftValue, "\n") !== \false || \strpos($rightValue, "\n") !== \false) {
            return null;
        }
        $joinedStringValue = $leftValue . $rightValue;
        if (StringUtils::isMatch($joinedStringValue, self::ASCII_REGEX)) {
            return null;
        }
        if (Strings::length($joinedStringValue) >= self::LINE_BREAK_POINT) {
            return null;
        }
        return new String_($joinedStringValue);
    }
}
