<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer;
use Rector\DowngradePhp73\Tokenizer\TrailingCommaRemover;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\DowngradeTrailingCommasInFunctionCallsRectorTest
 */
final class DowngradeTrailingCommasInFunctionCallsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private FollowedByCommaAnalyzer $followedByCommaAnalyzer;
    /**
     * @readonly
     */
    private TrailingCommaRemover $trailingCommaRemover;
    public function __construct(FollowedByCommaAnalyzer $followedByCommaAnalyzer, TrailingCommaRemover $trailingCommaRemover)
    {
        $this->followedByCommaAnalyzer = $followedByCommaAnalyzer;
        $this->trailingCommaRemover = $trailingCommaRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove trailing commas in function calls', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class, MethodCall::class, StaticCall::class, New_::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall|New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if ($args === []) {
            return null;
        }
        foreach ($args as $arg) {
            // reprinted, needs to remove from call like itself
            if ($arg->getEndTokenPos() < 0) {
                $hasChanged = $this->trailingCommaRemover->removeFromCallLike($this->file, $node);
                if ($hasChanged) {
                    return $node;
                }
                return null;
            }
        }
        $lastArgKey = \count($args) - 1;
        $lastArg = $args[$lastArgKey];
        if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $lastArg)) {
            return null;
        }
        $this->trailingCommaRemover->remove($this->file, $lastArg);
        return $node;
    }
}
