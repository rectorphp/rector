<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractMockeryRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\MockeryToProphecy\Tests\Rector\MethodCall\MockeryToProphecyRector\MockeryToProphecyRectorTest
 */
final class MockeryCreateMockToProphizeRector extends AbstractMockeryRector
{
    /**
     * @var array<string, class-string>
     */
    private $mockVariableTypesByNames;

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if ($node instanceof ClassMethod) {
            $this->replaceMockCreationsAndCollectVariableNames($node);
            $this->revealMockArguments($node);
        }

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes mockery mock creation to Prophesize',
            [
                new CodeSample(
                    <<<'PHP'
$mock = \Mockery::mock(\'MyClass\');
$service = new Service();
$service->injectDependency($mock);
PHP,
                    <<<'PHP'
 $mock = $this->prophesize(\'MyClass\');

$service = new Service();
$service->injectDependency($mock->reveal());
PHP
                ),
            ]
        );
    }

    private function replaceMockCreationsAndCollectVariableNames(ClassMethod $node): void
    {
        if ($node->stmts === null) {
            return;
        }

        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (! $this->isStaticCallNamed($node, 'Mockery', 'mock')) {
                return null;
            }

            /** @var StaticCall $node */
            $this->collectMockVariableName($node);

            if ($node->getAttribute('parentNode') instanceof Node\Arg) {
                return $this->createMethodCall($this->prophesizeCreateMock($node), 'reveal');
            }

            /** @var StaticCall $node */
            return $this->prophesizeCreateMock($node);
        });
    }

    private function collectMockVariableName(StaticCall $staticCall): void
    {
        $parentNode = $staticCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Assign) {
            return;
        }

        if (! $parentNode->var instanceof Variable) {
            return;
        }

        /** @var Variable $variable */
        $variable = $parentNode->var;

        /** @var string $variableName */
        $variableName = $this->getName($variable);

        $type = $staticCall->args[0]->value;
        $mockedType = $this->getValue($type);

        $this->mockVariableTypesByNames[$variableName] = $mockedType;
    }

    private function prophesizeCreateMock(StaticCall $node): MethodCall
    {
        return $this->createLocalMethodCall('prophesize', [$node->args[0]]);
    }

    private function revealMockArguments(ClassMethod $node): void
    {
        if ($node->stmts === null) {
            return;
        }

        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (! $node instanceof Node\Arg) {
                return null;
            }

            if (! $node->value instanceof Variable) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->getName($node->value);

            if (! isset($this->mockVariableTypesByNames[$variableName])) {
                return null;
            }

            return $this->createMethodCall($node->value, 'reveal');
        });
    }
}
