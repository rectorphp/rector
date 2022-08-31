<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\Tokenizer\ParenthesizedNestedTernaryAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration74.deprecated.php
 * @changelog https://3v4l.org/vhdlJ
 * @see \Rector\Tests\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector\ParenthesizeNestedTernaryRectorTest
 */
final class ParenthesizeNestedTernaryRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php74\Tokenizer\ParenthesizedNestedTernaryAnalyzer
     */
    private $parenthesizedNestedTernaryAnalyzer;
    public function __construct(ParenthesizedNestedTernaryAnalyzer $parenthesizedNestedTernaryAnalyzer)
    {
        $this->parenthesizedNestedTernaryAnalyzer = $parenthesizedNestedTernaryAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_NESTED_TERNARY;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add parentheses to nested ternary', [new CodeSample(<<<'CODE_SAMPLE'
$value = $a ? $b : $a ?: null;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = ($a ? $b : $a) ?: null;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Ternary) {
            return null;
        }
        if ($this->parenthesizedNestedTernaryAnalyzer->isParenthesized($this->file, $parentNode)) {
            return null;
        }
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
