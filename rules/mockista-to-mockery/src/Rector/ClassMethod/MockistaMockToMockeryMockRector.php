<?php

declare(strict_types=1);

namespace Rector\MockistaToMockery\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\MockeryToProphecy\Collector\MockVariableCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector\MockistaMockToMockeryMockRectorTest
 */
final class MockistaMockToMockeryMockRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const METHODS_TO_REMOVE = ['freeze', 'assertExpectations'];

    /**
     * @var string[]
     */
    private $mockVariableTypesByNames = [];

    /**
     * @var MockVariableCollector
     */
    private $mockVariableCollector;

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(MockVariableCollector $mockVariableCollector, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->mockVariableCollector = $mockVariableCollector;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change functions to static calls, so composer can autoload them',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeTest
{
    public function run()
    {
        $mockUser = mock(User::class);
        $mockUser->getId()->once->andReturn(1);
        $mockUser->freeze();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeTest
{
    public function run()
    {
        $mockUser = Mockery::mock(User::class);
        $mockUser->expects()->getId()->once()->andReturn(1);
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        $this->replaceMockWithMockerMockAndCollectMockVariableName($node);
        $this->replaceMethodCallOncePropertyFetch($node);
        $this->removeUnusedMethodCalls($node);
        $this->replaceMethodCallWithExpects($node);

        $this->switchWithAnyArgsAndOnceTwice($node);

        return $node;
    }

    private function replaceMockWithMockerMockAndCollectMockVariableName(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node): ?StaticCall {
            if (! $this->isFuncCallName($node, 'mock')) {
                return null;
            }

            /** @var FuncCall $node */
            $collectedVariableTypesByNames = $this->mockVariableCollector->collectMockVariableName($node);
            $this->mockVariableTypesByNames = array_merge(
                $this->mockVariableTypesByNames,
                $collectedVariableTypesByNames
            );

            return $this->nodeFactory->createStaticCall('Mockery', 'mock', $node->args);
        });
    }

    /**
     * $mock->getMethod()->once
     * ↓
     * $mock->getMethod()->once()
     */
    private function replaceMethodCallOncePropertyFetch(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node): ?MethodCall {
                if (! $node instanceof PropertyFetch) {
                    return null;
                }

                if (! $this->isNames($node->name, ['once', 'twice'])) {
                    return null;
                }

                return new MethodCall($node->var, $node->name);
            }
        );
    }

    private function removeUnusedMethodCalls(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $this->isMethodCallOrPropertyFetchOnMockVariable($node)) {
                return null;
            }

            /** @var PropertyFetch|MethodCall $node */
            if (! $this->isNames($node->name, self::METHODS_TO_REMOVE)) {
                return null;
            }

            $this->removeNode($node);
        });
    }

    /**
     * $mock->getMethod()->once()
     * ↓
     * $mock->expects()->getMethod()->once()
     */
    private function replaceMethodCallWithExpects(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node): ?MethodCall {
            if (! $this->isMethodCallOrPropertyFetchOnMockVariable($node)) {
                return null;
            }

            // skip assigns
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parent instanceof Assign) {
                return null;
            }

            /** @var MethodCall|PropertyFetch $node */
            if ($this->isNames($node->name, self::METHODS_TO_REMOVE)) {
                return null;
            }

            if ($this->isNames($node->name, ['expects', 'allows'])) {
                return null;
            }

            // probably method mock
            $expectedMethodCall = new MethodCall($node->var, 'expects');
            $methodCall = new MethodCall($expectedMethodCall, $node->name);

            if ($node instanceof PropertyFetch) {
                return $methodCall;
            }

            $methodCall->args = $node->args;

            return $this->decorateWithAnyArgs($node, $methodCall);
        });
    }

    /**
     * Order correction for @see replaceMethodCallWithExpects()
     */
    private function switchWithAnyArgsAndOnceTwice(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isNames($node->name, ['once', 'twice'])) {
                return;
            }

            if (! $node->var instanceof MethodCall) {
                return null;
            }

            /** @var MethodCall $previousMethodCall */
            $previousMethodCall = $node->var;
            if (! $this->isName($previousMethodCall->name, 'withAnyArgs')) {
                return null;
            }

            [$node->name, $previousMethodCall->name] = [$previousMethodCall->name, $node->name];
        });
    }

    private function isMethodCallOrPropertyFetchOnMockVariable(Node $node): bool
    {
        if (! $node instanceof MethodCall && ! $this->isPropertyFetchDisguisedAsMethodCall($node)) {
            return false;
        }

        /** @var MethodCall|PropertyFetch $node */
        if (! $node->var instanceof Variable) {
            return false;
        }

        /** @var string $variableName */
        $variableName = $this->getName($node->var);

        return isset($this->mockVariableTypesByNames[$variableName]);
    }

    /**
     * $mock->someMethodWithArgs()->once()
     * ↓
     * $mock->expects()->someMethodWithArgs()->withAnyArgs()->once()
     */
    private function decorateWithAnyArgs(MethodCall $originalMethodCall, MethodCall $expectsMethodCall): MethodCall
    {
        $variableName = $this->getName($originalMethodCall->var);
        $mockVariableType = $this->mockVariableTypesByNames[$variableName];

        $methodName = $this->getName($originalMethodCall->name);
        if ($methodName === null) {
            return $expectsMethodCall;
        }

        if (! method_exists($mockVariableType, $methodName)) {
            return $expectsMethodCall;
        }

        $reflectionMethod = new ReflectionMethod($mockVariableType, $methodName);
        if ($reflectionMethod->getNumberOfRequiredParameters() === 0) {
            return $expectsMethodCall;
        }

        return new MethodCall($expectsMethodCall, 'withAnyArgs');
    }

    private function isPropertyFetchDisguisedAsMethodCall(Node $node): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        if ($node->var instanceof MethodCall) {
            return false;
        }

        $variableName = $this->getName($node->var);
        if (! isset($this->mockVariableTypesByNames[$variableName])) {
            return false;
        }

        $mockVariableType = $this->mockVariableTypesByNames[$variableName];
        $propertyName = $this->getName($node->name);
        if ($propertyName === null) {
            return false;
        }

        return method_exists($mockVariableType, $propertyName);
    }
}
