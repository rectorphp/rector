<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/first_class_callable_syntax
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\DowngradeFirstClassCallableSyntaxRectorTest
 */
final class DowngradeFirstClassCallableSyntaxRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace variadic placeholders usage by Closure::fromCallable()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$cb = strlen(...);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$cb = \Closure::fromCallable('strlen');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\StaticCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $callback = $this->createCallback($node);
        return $this->createClosureFromCallableCall($callback);
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function shouldSkip($node) : bool
    {
        if (\count($node->getRawArgs()) !== 1) {
            return \true;
        }
        return !$node->args[0] instanceof \PhpParser\Node\VariadicPlaceholder;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function createCallback($node) : \PhpParser\Node\Expr
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $node->name instanceof \PhpParser\Node\Name ? new \PhpParser\Node\Scalar\String_($node->name->toString()) : $node->name;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            $object = $node->var;
            $method = $node->name instanceof \PhpParser\Node\Identifier ? new \PhpParser\Node\Scalar\String_($node->name->toString()) : $node->name;
            return new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($object), new \PhpParser\Node\Expr\ArrayItem($method)]);
        }
        // StaticCall
        $class = $node->class instanceof \PhpParser\Node\Name ? new \PhpParser\Node\Expr\ClassConstFetch($node->class, 'class') : $node->class;
        $method = $node->name instanceof \PhpParser\Node\Identifier ? new \PhpParser\Node\Scalar\String_($node->name->toString()) : $node->name;
        return $this->nodeFactory->createArray([$class, $method]);
    }
    private function createClosureFromCallableCall(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\StaticCall
    {
        return new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name\FullyQualified('Closure'), 'fromCallable', [new \PhpParser\Node\Arg($expr)]);
    }
}
