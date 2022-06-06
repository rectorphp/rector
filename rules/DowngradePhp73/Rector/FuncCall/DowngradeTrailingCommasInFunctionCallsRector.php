<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\DowngradeTrailingCommasInFunctionCallsRectorTest
 */
final class DowngradeTrailingCommasInFunctionCallsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer
     */
    private $followedByCommaAnalyzer;
    public function __construct(\Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer $followedByCommaAnalyzer)
    {
        $this->followedByCommaAnalyzer = $followedByCommaAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove trailing commas in function calls', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value)
    {
        $compacted = compact(
            'posts',
            'units',
        );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value)
    {
        $compacted = compact(
            'posts',
            'units'
        );
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
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall|New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->args) {
            \end($node->args);
            $lastArgumentPosition = \key($node->args);
            $last = $node->args[$lastArgumentPosition];
            if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $last)) {
                return null;
            }
            // remove comma
            $last->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::FUNC_ARGS_TRAILING_COMMA, \false);
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            return $node;
        }
        return null;
    }
}
