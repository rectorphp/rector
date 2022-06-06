<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp81\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\VariadicPlaceholder;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
        return !$node->args[0] instanceof VariadicPlaceholder;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function createCallback($node) : Expr
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
    private function createClosureFromCallableCall(Expr $expr) : StaticCall
    {
        return new StaticCall(new FullyQualified('Closure'), 'fromCallable', [new Arg($expr)]);
    }
}
