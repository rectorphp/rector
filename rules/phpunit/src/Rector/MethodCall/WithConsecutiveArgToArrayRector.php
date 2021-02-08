<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ArrayType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://stackoverflow.com/questions/10954107/phpunit-how-do-i-mock-multiple-method-calls-with-multiple-arguments/28045531#28045531
 * @see https://github.com/sebastianbergmann/phpunit/commit/72098d80f0cfc06c7e0652d331602685ce5b4b51
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\WithConsecutiveArgToArrayRector\WithConsecutiveArgToArrayRectorTest
 */
final class WithConsecutiveArgToArrayRector extends AbstractRector
{
    /**
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    public function __construct(MethodCallManipulator $methodCallManipulator)
    {
        $this->methodCallManipulator = $methodCallManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Split withConsecutive() arg to array', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($one, $two)
    {
    }
}

class SomeTestCase extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $someClassMock = $this->createMock(SomeClass::class);
        $someClassMock
            ->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(1, 2, 3, 5);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($one, $two)
    {
    }
}

class SomeTestCase extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $someClassMock = $this->createMock(SomeClass::class);
        $someClassMock
            ->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive([1, 2], [3, 5]);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'withConsecutive')) {
            return null;
        }

        if ($this->areAllArgArrayTypes($node)) {
            return null;
        }

        // is a mock?
        if (! $this->isObjectType($node, 'PHPUnit\Framework\MockObject\Builder\InvocationMocker')) {
            return null;
        }

        $mockClass = $this->inferMockedClassName($node);
        if ($mockClass === null) {
            return null;
        }

        $mockMethod = $this->inferMockedMethodName($node);
        $reflectionMethod = new ReflectionMethod($mockClass, $mockMethod);
        $numberOfParameters = $reflectionMethod->getNumberOfParameters();

        $values = [];
        foreach ($node->args as $arg) {
            $values[] = $arg->value;
        }

        // simple check argument count fits to method required args
        if (count($values) % $numberOfParameters !== 0) {
            return null;
        }

        $node->args = [];

        // split into chunks of X parameters
        $valueChunks = array_chunk($values, $numberOfParameters);
        foreach ($valueChunks as $valueChunk) {
            $node->args[] = new Arg($this->nodeFactory->createArray($valueChunk));
        }

        return $node;
    }

    private function areAllArgArrayTypes(MethodCall $methodCall): bool
    {
        foreach ($methodCall->args as $arg) {
            $argumentStaticType = $this->getStaticType($arg->value);
            if ($argumentStaticType instanceof ArrayType) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function inferMockedClassName(MethodCall $methodCall): ?string
    {
        $variable = $this->findRootVariableOfChainCall($methodCall);
        if (! $variable instanceof Variable) {
            return null;
        }

        // look for "$this->createMock(X)"
        $assignToVariable = $this->methodCallManipulator->findAssignToVariable($variable);
        if (! $assignToVariable instanceof Assign) {
            return null;
        }

        if ($assignToVariable->expr instanceof MethodCall) {
            /** @var MethodCall $assignedMethodCall */
            $assignedMethodCall = $assignToVariable->expr;
            if ($this->isName($assignedMethodCall->var, 'this') && $this->isName(
                $assignedMethodCall->name,
                'createMock'
            )) {
                $firstArgumentValue = $assignedMethodCall->args[0]->value;
                return $this->valueResolver->getValue($firstArgumentValue);
            }
        }

        return null;
    }

    private function inferMockedMethodName(MethodCall $methodCall): string
    {
        $previousMethodCalls = $this->methodCallManipulator->findMethodCallsIncludingChain($methodCall);
        foreach ($previousMethodCalls as $previousMethodCall) {
            if (! $this->isName($previousMethodCall->name, 'method')) {
                continue;
            }

            $firstArgumentValue = $previousMethodCall->args[0]->value;
            if (! $firstArgumentValue instanceof String_) {
                continue;
            }

            return $firstArgumentValue->value;
        }

        throw new ShouldNotHappenException();
    }

    private function findRootVariableOfChainCall(MethodCall $methodCall): ?Variable
    {
        $currentMethodCallee = $methodCall->var;
        while (! $currentMethodCallee instanceof Variable) {
            $currentMethodCallee = $currentMethodCallee->var;
        }

        return $currentMethodCallee;
    }
}
