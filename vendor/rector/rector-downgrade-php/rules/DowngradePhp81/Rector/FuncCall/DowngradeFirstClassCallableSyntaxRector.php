<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/first_class_callable_syntax
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector\DowngradeFirstClassCallableSyntaxRectorTest
 */
final class DowngradeFirstClassCallableSyntaxRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace variadic placeholders usage by Closure::fromCallable()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?StaticCall
    {
        if (!$node->isFirstClassCallable()) {
            return null;
        }
        $callbackExpr = $this->createCallback($node);
        return $this->createClosureFromCallableCall($callbackExpr);
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     * @return \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr
     */
    private function createCallback($node)
    {
        if ($node instanceof FuncCall) {
            return $node->name instanceof Name ? new String_($node->name->toString()) : $node->name;
        }
        if ($node instanceof MethodCall) {
            $object = $node->var;
            $method = $node->name instanceof Identifier ? new String_($node->name->toString()) : $node->name;
            return new Array_([new ArrayItem($object), new ArrayItem($method)]);
        }
        // StaticCall
        $class = $node->class instanceof Name ? new ClassConstFetch($node->class, 'class') : $node->class;
        $method = $node->name instanceof Identifier ? new String_($node->name->toString()) : $node->name;
        return $this->nodeFactory->createArray([$class, $method]);
    }
    /**
     * @param \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr $expr
     */
    private function createClosureFromCallableCall($expr) : StaticCall
    {
        return new StaticCall(new FullyQualified('Closure'), 'fromCallable', [new Arg($expr)]);
    }
}
