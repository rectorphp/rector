<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\Tokenizer\FollowedByCurlyBracketAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration74.deprecated.php
 * @see \Rector\Tests\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector\CurlyToSquareBracketArrayStringRectorTest
 */
final class CurlyToSquareBracketArrayStringRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php74\Tokenizer\FollowedByCurlyBracketAnalyzer
     */
    private $followedByCurlyBracketAnalyzer;
    public function __construct(FollowedByCurlyBracketAnalyzer $followedByCurlyBracketAnalyzer)
    {
        $this->followedByCurlyBracketAnalyzer = $followedByCurlyBracketAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_CURLY_BRACKET_ARRAY_STRING;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change curly based array and string to square bracket', [new CodeSample(<<<'CODE_SAMPLE'
$string = 'test';
echo $string{0};
$array = ['test'];
echo $array{0};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$string = 'test';
echo $string[0];
$array = ['test'];
echo $array[0];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->followedByCurlyBracketAnalyzer->isFollowed($this->file, $node)) {
            return null;
        }
        // re-draw the ArrayDimFetch to use [] bracket
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
