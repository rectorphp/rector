<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\Application\File;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector\CurlyToSquareBracketArrayStringRectorTest
 */
final class CurlyToSquareBracketArrayStringRector extends AbstractRector implements MinPhpVersionInterface
{
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
        if (!$this->isFollowedByCurlyBracket($this->file, $node)) {
            return null;
        }
        // re-draw the ArrayDimFetch to use [] bracket
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    private function isFollowedByCurlyBracket(File $file, ArrayDimFetch $arrayDimFetch) : bool
    {
        $oldTokens = $file->getOldTokens();
        $endTokenPost = $arrayDimFetch->getEndTokenPos();
        if (isset($oldTokens[$endTokenPost]) && $oldTokens[$endTokenPost] === '}') {
            $startTokenPost = $arrayDimFetch->getStartTokenPos();
            return !(isset($oldTokens[$startTokenPost][1]) && $oldTokens[$startTokenPost][1] === '${');
        }
        return \false;
    }
}
