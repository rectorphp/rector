<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\ClosureFromCallableToFirstClassCallableRector\ClosureFromCallableToFirstClassCallableRectorTest
 */
final class ClosureFromCallableToFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `Closure::fromCallable()` to first class callable syntax', [new CodeSample('Closure::fromCallable([$obj, \'method\']);', '$obj->method(...);'), new CodeSample("Closure::fromCallable('trim');", 'trim(...);'), new CodeSample("Closure::fromCallable(['SomeClass', 'staticMethod']);", 'SomeClass::staticMethod(...);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $arg = $node->args[0];
        if (!$arg instanceof Arg) {
            return null;
        }
        if ($arg->value instanceof String_) {
            return new FuncCall($this->toFullyQualified($arg->value->value), [new VariadicPlaceholder()]);
        }
        if ($arg->value instanceof Array_) {
            if (!array_key_exists(0, $arg->value->items) || !array_key_exists(1, $arg->value->items) || !$arg->value->items[1]->value instanceof String_) {
                return null;
            }
            if ($arg->value->items[0]->value instanceof Variable) {
                return new MethodCall($arg->value->items[0]->value, $arg->value->items[1]->value->value, [new VariadicPlaceholder()]);
            }
            if ($arg->value->items[0]->value instanceof String_) {
                $classNode = new FullyQualified($arg->value->items[0]->value->value);
            } elseif ($arg->value->items[0]->value instanceof ClassConstFetch) {
                if ($arg->value->items[0]->value->class instanceof Expr) {
                    return null;
                }
                $classNode = new FullyQualified($arg->value->items[0]->value->class->name);
            } elseif ($arg->value->items[0]->value instanceof FullyQualified) {
                $classNode = new FullyQualified($arg->value->items[0]->value->name);
            } else {
                return null;
            }
            return new StaticCall($classNode, $arg->value->items[1]->value->value, [new VariadicPlaceholder()]);
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FIRST_CLASS_CALLABLE_SYNTAX;
    }
    public function shouldSkip(StaticCall $staticCall): bool
    {
        if (!$staticCall->class instanceof Name) {
            return \true;
        }
        if (!$this->isName($staticCall->class, 'Closure')) {
            return \true;
        }
        if (!$staticCall->name instanceof Identifier || $staticCall->name->name !== 'fromCallable') {
            return \true;
        }
        if ($staticCall->isFirstClassCallable()) {
            return \true;
        }
        $args = $staticCall->getArgs();
        return count($args) !== 1;
    }
    public function toFullyQualified(string $functionName): FullyQualified
    {
        // in case there's already a \ prefix, remove it
        $functionName = ltrim($functionName, '\\');
        return new FullyQualified($functionName);
    }
}
