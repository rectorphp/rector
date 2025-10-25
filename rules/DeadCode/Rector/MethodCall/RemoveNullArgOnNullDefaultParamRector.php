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
        if ($node->getArgs() === []) {
            return null;
        }
        $nullPositions = $this->callLikeParamDefaultResolver->resolveNullPositions($node);
        if ($nullPositions === []) {
            return null;
        }
        $hasChanged = \false;
        $args = $node->getArgs();
        $lastArgPosition = count($args) - 1;
        for ($position = $lastArgPosition; $position >= 0; --$position) {
            if (!isset($args[$position])) {
                continue;
            }
            $arg = $args[$position];
            if ($arg->unpack) {
                break;
            }
            // stop when found named arg and position not match
            if ($arg->name instanceof Identifier && $position !== $this->callLikeParamDefaultResolver->resolvePositionParameterByName($node, $arg->name->toString())) {
                break;
            }
            if (!$this->valueResolver->isNull($arg->value)) {
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
