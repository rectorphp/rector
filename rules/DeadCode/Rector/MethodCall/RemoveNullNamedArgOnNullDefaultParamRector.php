<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\DeadCode\NodeAnalyzer\CallLikeParamDefaultResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveNullNamedArgOnNullDefaultParamRector\RemoveNullNamedArgOnNullDefaultParamRectorTest
 */
final class RemoveNullNamedArgOnNullDefaultParamRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private CallLikeParamDefaultResolver $callLikeParamDefaultResolver;
    public function __construct(ValueResolver $valueResolver, CallLikeParamDefaultResolver $callLikeParamDefaultResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->callLikeParamDefaultResolver = $callLikeParamDefaultResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove named null argument, where null is already a default param value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function call(ExternalClass $externalClass)
    {
        $externalClass->execute(value: 1, someClass: null);
    }
}

class ExternalClass
{
    public function execute(int $value, ?SomeClass $someClass = null)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function call(ExternalClass $externalClass)
    {
        $externalClass->execute(value: 1);
    }
}

class ExternalClass
{
    public function execute(int $value, ?SomeClass $someClass = null)
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, New_::class, FuncCall::class];
    }
    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     * @return \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall|null
     */
    public function refactor(Node $node)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if ($args === []) {
            return null;
        }
        $hasNamedArg = \false;
        foreach ($args as $arg) {
            // unpack hides which parameters are bound, so removal is not safe
            if ($arg->unpack) {
                return null;
            }
            if ($arg->name instanceof Identifier) {
                $hasNamedArg = \true;
            }
        }
        if (!$hasNamedArg) {
            return null;
        }
        $nullPositions = $this->callLikeParamDefaultResolver->resolveNullPositions($node);
        if ($nullPositions === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($args as $argPosition => $arg) {
            // only named args are removed here; in any order, remaining args still bind by name
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->valueResolver->isNull($arg->value)) {
                continue;
            }
            $parameterPosition = $this->callLikeParamDefaultResolver->resolvePositionParameterByName($node, $arg->name->toString());
            if ($parameterPosition === null) {
                continue;
            }
            if (!in_array($parameterPosition, $nullPositions, \true)) {
                continue;
            }
            unset($node->args[$argPosition]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
