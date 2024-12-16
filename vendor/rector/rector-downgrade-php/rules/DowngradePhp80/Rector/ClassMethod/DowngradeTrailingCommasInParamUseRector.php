<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer;
use Rector\DowngradePhp73\Tokenizer\TrailingCommaRemover;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\DowngradeTrailingCommasInParamUseRectorTest
 */
final class DowngradeTrailingCommasInParamUseRector extends AbstractRector
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
        return new RuleDefinition('Remove trailing commas in param or use list', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value1, string $value2,)
    {
        function (string $value1, string $value2,) {
        };

        function () use ($value1, $value2,) {
        };
    }
}

function inFunction(string $value1, string $value2,)
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value1, string $value2)
    {
        function (string $value1, string $value2) {
        };

        function () use ($value1, $value2) {
        };
    }
}

function inFunction(string $value1, string $value2)
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Closure) {
            $this->processUses($node);
        }
        return $this->processParams($node);
    }
    private function processUses(Closure $node) : ?Node
    {
        if ($node->uses === []) {
            return null;
        }
        return $this->cleanTrailingComma($node, $node->uses);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function processParams($node) : ?Node
    {
        if ($node->params === []) {
            return null;
        }
        return $this->cleanTrailingComma($node, $node->params);
    }
    /**
     * @param ClosureUse[]|Param[] $array
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function cleanTrailingComma($node, array $array) : ?Node
    {
        $lastPosition = \array_key_last($array);
        $last = $array[$lastPosition];
        if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $last)) {
            return null;
        }
        $this->trailingCommaRemover->remove($this->file, $last);
        return $node;
    }
}
