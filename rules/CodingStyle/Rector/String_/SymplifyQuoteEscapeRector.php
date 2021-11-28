<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector\SymplifyQuoteEscapeRectorTest
 */
final class SymplifyQuoteEscapeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/qEkCe9/2
     */
    private const ESCAPED_CHAR_REGEX = '#\\\\|\\$|\\n|\\t#sim';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Prefer quote that are not inside the string', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
         $name = "\" Tom";
         $name = '\' Sara';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
         $name = '" Tom';
         $name = "' Sara";
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
        return [\PhpParser\Node\Scalar\String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Scalar\String_
    {
        $doubleQuoteCount = \substr_count($node->value, '"');
        $singleQuoteCount = \substr_count($node->value, "'");
        $kind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if ($kind === \PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED) {
            $this->processSingleQuoted($node, $doubleQuoteCount, $singleQuoteCount);
        }
        $quoteKind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if ($quoteKind === \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED) {
            $this->processDoubleQuoted($node, $singleQuoteCount, $doubleQuoteCount);
        }
        return $node;
    }
    private function processSingleQuoted(\PhpParser\Node\Scalar\String_ $string, int $doubleQuoteCount, int $singleQuoteCount) : void
    {
        if ($doubleQuoteCount === 0 && $singleQuoteCount > 0) {
            // contains chars that will be newly escaped
            if ($this->isMatchEscapedChars($string->value)) {
                return;
            }
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED);
            // invoke override
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        }
    }
    private function processDoubleQuoted(\PhpParser\Node\Scalar\String_ $string, int $singleQuoteCount, int $doubleQuoteCount) : void
    {
        if ($singleQuoteCount === 0 && $doubleQuoteCount > 0) {
            // contains chars that will be newly escaped
            if ($this->isMatchEscapedChars($string->value)) {
                return;
            }
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED);
            // invoke override
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        }
    }
    private function isMatchEscapedChars(string $string) : bool
    {
        return \Rector\Core\Util\StringUtils::isMatch($string, self::ESCAPED_CHAR_REGEX);
    }
}
