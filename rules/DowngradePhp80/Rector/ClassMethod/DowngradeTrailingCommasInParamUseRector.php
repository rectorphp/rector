<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\DowngradeTrailingCommasInParamUseRectorTest
 */
final class DowngradeTrailingCommasInParamUseRector extends \Rector\Core\Rector\AbstractRector
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove trailing commas in param or use list', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|FuncCall|MethodCall|StaticCall|New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\StaticCall || $node instanceof \PhpParser\Node\Expr\New_) {
            /** @var MethodCall|FuncCall|StaticCall|New_ $node */
            return $this->processArgs($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\Closure) {
            $this->processUses($node);
        }
        return $this->processParams($node);
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processArgs($node) : ?\PhpParser\Node
    {
        $args = $node->args;
        if ($args === []) {
            return null;
        }
        return $this->cleanTrailingComma($node, $args);
    }
    private function processUses(\PhpParser\Node\Expr\Closure $node) : void
    {
        if ($node->uses === []) {
            return;
        }
        $this->cleanTrailingComma($node, $node->uses);
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function processParams($node) : ?\PhpParser\Node
    {
        if ($node->params === []) {
            return null;
        }
        return $this->cleanTrailingComma($node, $node->params);
    }
    /**
     * @param ClosureUse[]|Param[]|Arg[] $array
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function cleanTrailingComma($node, array $array) : ?\PhpParser\Node
    {
        \end($array);
        $lastPosition = \key($array);
        $last = $array[$lastPosition];
        if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $last)) {
            return null;
        }
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        $last->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::FUNC_ARGS_TRAILING_COMMA, \false);
        return $node;
    }
}
