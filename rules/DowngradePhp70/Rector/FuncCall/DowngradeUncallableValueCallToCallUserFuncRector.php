<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/uniform_variable_syntax
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector\DowngradeUncallableValueCallToCallUserFuncRectorTest
 */
final class DowngradeUncallableValueCallToCallUserFuncRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const INDIRECT_CALLABLE_EXPR = [
        // Interpreted as MethodCall without parentheses.
        \PhpParser\Node\Expr\PropertyFetch::class,
        // Interpreted as StaticCall without parentheses.
        \PhpParser\Node\Expr\StaticPropertyFetch::class,
        \PhpParser\Node\Expr\Closure::class,
        // The first function call does not even need to be wrapped in parentheses
        // but PHP 5 still does not like curried functions like `f($args)($moreArgs)`.
        \PhpParser\Node\Expr\FuncCall::class,
    ];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade calling a value that is not directly callable in PHP 5 (property, static property, closure, …) to call_user_func.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class Foo
{
    /** @var callable */
    public $handler;
    /** @var callable */
    public static $staticHandler;
}

$foo = new Foo;
($foo->handler)(/* args */);
($foo::$staticHandler)(41);

(function() { /* … */ })();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class Foo
{
    /** @var callable */
    public $handler;
    /** @var callable */
    public static $staticHandler;
}

$foo = new Foo;
call_user_func($foo->handler, /* args */);
call_user_func($foo::$staticHandler, 41);

call_user_func(function() { /* … */ });
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\FuncCall
    {
        if ($node->name instanceof \PhpParser\Node\Name) {
            return null;
        }
        if (!$this->isNotDirectlyCallableInPhp5($node->name)) {
            return null;
        }
        $args = \array_merge([new \PhpParser\Node\Arg($node->name)], $node->args);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('call_user_func'), $args);
    }
    private function isNotDirectlyCallableInPhp5(\PhpParser\Node\Expr $expr) : bool
    {
        return \in_array(\get_class($expr), self::INDIRECT_CALLABLE_EXPR, \true);
    }
}
