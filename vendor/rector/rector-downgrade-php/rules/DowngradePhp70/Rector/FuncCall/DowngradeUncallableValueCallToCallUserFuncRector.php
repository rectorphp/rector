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
final class DowngradeUncallableValueCallToCallUserFuncRector extends AbstractRector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const INDIRECT_CALLABLE_EXPR = [
        // Interpreted as MethodCall without parentheses.
        PropertyFetch::class,
        // Interpreted as StaticCall without parentheses.
        StaticPropertyFetch::class,
        Closure::class,
        // The first function call does not even need to be wrapped in parentheses
        // but PHP 5 still does not like curried functions like `f($args)($moreArgs)`.
        FuncCall::class,
    ];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade calling a value that is not directly callable in PHP 5 (property, static property, closure, …) to call_user_func.', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if ($node->name instanceof Name) {
            return null;
        }
        if (!$this->isNotDirectlyCallableInPhp5($node->name)) {
            return null;
        }
        $args = \array_merge([new Arg($node->name)], $node->args);
        return new FuncCall(new Name('call_user_func'), $args);
    }
    private function isNotDirectlyCallableInPhp5(Expr $expr) : bool
    {
        return \in_array(\get_class($expr), self::INDIRECT_CALLABLE_EXPR, \true);
    }
}
