<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
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
    public function __construct()
    {
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `Closure::fromCallable()` to first class callable syntax', [new CodeSample('Closure::fromCallable([$obj, \'method\']);', '$obj->method(...);'), new CodeSample('Closure::fromCallable(\'trim\');', 'trim(...);'), new CodeSample('Closure::fromCallable([\'SomeClass\', \'staticMethod\']);', 'SomeClass::staticMethod(...);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Node\Expr\StaticCall::class];
    }
    /**
     * @param Node\Expr\StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $arg = $node->args[0];
        if (!$arg instanceof Node\Arg) {
            return null;
        }
        if ($arg->value instanceof Node\Scalar\String_) {
            return new Node\Expr\FuncCall($this->toFullyQualified($arg->value->value), [new Node\VariadicPlaceholder()]);
        }
        if ($arg->value instanceof Node\Expr\Array_) {
            if (!\array_key_exists(0, $arg->value->items) || !\array_key_exists(1, $arg->value->items) || !$arg->value->items[1]->value instanceof Node\Scalar\String_) {
                return null;
            }
            if ($arg->value->items[0]->value instanceof Node\Expr\Variable) {
                return new Node\Expr\MethodCall($arg->value->items[0]->value, $arg->value->items[1]->value->value, [new Node\VariadicPlaceholder()]);
            }
            if ($arg->value->items[0]->value instanceof Node\Scalar\String_) {
                $classNode = new Node\Name\FullyQualified($arg->value->items[0]->value->value);
            } elseif ($arg->value->items[0]->value instanceof Node\Expr\ClassConstFetch) {
                if ($arg->value->items[0]->value->class instanceof Node\Expr) {
                    return null;
                }
                $classNode = new Node\Name\FullyQualified($arg->value->items[0]->value->class->name);
            } elseif ($arg->value->items[0]->value instanceof Node\Name\FullyQualified) {
                $classNode = new Node\Name\FullyQualified($arg->value->items[0]->value->name);
            } else {
                return null;
            }
            return new Node\Expr\StaticCall($classNode, $arg->value->items[1]->value->value, [new Node\VariadicPlaceholder()]);
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::FIRST_CLASS_CALLABLE_SYNTAX;
    }
    public function shouldSkip(Node\Expr\StaticCall $node) : bool
    {
        if (!$node->class instanceof Node\Name) {
            return \true;
        }
        if (!$this->isName($node->class, 'Closure')) {
            return \true;
        }
        if (!$node->name instanceof Node\Identifier || $node->name->name !== 'fromCallable') {
            return \true;
        }
        if ($node->isFirstClassCallable()) {
            return \true;
        }
        $args = $node->getArgs();
        if (\count($args) !== 1) {
            return \true;
        }
        return \false;
    }
    public function toFullyQualified(string $functionName) : Node\Name\FullyQualified
    {
        // in case there's already a \ prefix, remove it
        $functionName = \ltrim($functionName, '\\');
        return new Node\Name\FullyQualified($functionName);
    }
}
