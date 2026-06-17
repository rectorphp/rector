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
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveNullArgOnNullDefaultParamRector\RemoveNullArgOnNullDefaultParamRectorTest
 */
final class RemoveNullArgOnNullDefaultParamRector extends AbstractRector
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
        return new RuleDefinition('Remove default null argument, where null is already a default param value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function call(ExternalClass $externalClass)
    {
        $externalClass->execute(null);
    }
}

class ExternalClass
{
    public function execute(?SomeClass $someClass = null)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'

class SomeClass
{
    public function call(ExternalClass $externalClass)
    {
        $externalClass->execute();
    }
}

class ExternalClass
{
    public function execute(?SomeClass $someClass = null)
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
        // named args are handled by RemoveNullNamedArgOnNullDefaultParamRector
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return null;
            }
            if ($arg->unpack) {
                return null;
            }
        }
        $nullPositions = $this->callLikeParamDefaultResolver->resolveNullPositions($node);
        if ($nullPositions === []) {
            return null;
        }
        $hasChanged = \false;
        for ($position = count($args) - 1; $position >= 0; --$position) {
            $arg = $args[$position];
            if (!$this->valueResolver->isNull($arg->value)) {
                // a named non-null argument can be skipped over: removing an earlier
                // named null argument still leaves the remaining named arguments validly bound
                if ($arg->name instanceof Identifier) {
                    continue;
                }
                break;
            }
            if (!in_array($position, $nullPositions, \true)) {
                break;
            }
            unset($node->args[$position]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
