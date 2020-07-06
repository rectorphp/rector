<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeManipulator\ChainMethodCallRootExtractor;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MockeryPredictionsToProphizeRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $mockVariableTypesByNames = [];

    /**
     * @var ChainMethodCallNodeAnalyzer
     */
    private $chainMethodCallNodeAnalyzer;

    /**
     * @var ChainMethodCallRootExtractor
     */
    private $chainMethodCallRootExtractor;

    public function __construct(
        ChainMethodCallNodeAnalyzer $chainMethodCallNodeAnalyzer,
        ChainMethodCallRootExtractor $chainMethodCallRootExtractor
    ) {
        $this->chainMethodCallNodeAnalyzer = $chainMethodCallNodeAnalyzer;
        $this->chainMethodCallRootExtractor = $chainMethodCallRootExtractor;
    }


    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$this->isInTestClass($node)) {
            return null;
        }

        if ($node instanceof ClassMethod) {
            $this->replaceMockCreationsAndCollectVariableNames($node);
            $this->replaceMockExpectations($node);
            $this->revealMockArguments($node);
        }

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes mockery close from test classes',
            [
                new CodeSample(
                    '$mock->shouldRecieve("someMethod")->never()',
                    '$mock->someMethod()->shouldNotBeCalled()'
                ),
            ]
        );
    }

    private function replaceMockCreationsAndCollectVariableNames(ClassMethod $node)
    {
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (!$this->isStaticCallNamed($node, 'Mockery', 'mock')) {
                return null;
            }

            $this->collectMockVariableName($node);

            if ($node->getAttribute('parentNode') instanceof Node\Arg) {
                return $this->createMethodCall($this->prophesizeCreateMock($node), 'reveal');
            }

            return $this->prophesizeCreateMock($node);
        });
    }

    private function collectMockVariableName(Node\Expr\StaticCall $staticCall): void
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


    /**
     * @param $node
     * @return MethodCall
     */
    private function prophesizeCreateMock($node): MethodCall
    {
        return $this->createLocalMethodCall('prophesize', [$node->args[0]]);
    }

    private function replaceMockExpectations(ClassMethod $node)
    {
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {

            if (!$node instanceof MethodCall) {
                return null;
            }

            if (! $this->chainMethodCallNodeAnalyzer->isLastChainMethodCall($node)) {
                return null;
            }

            $chainMethodCalls = $this->chainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($node);
            $assignAndRootExpr = $this->chainMethodCallRootExtractor->extractFromMethodCalls($chainMethodCalls);
            if (!$this->isMockVariable($assignAndRootExpr->getRootExpr())) {
                return null;
            }

            $chainMethodCalls = array_reverse($chainMethodCalls);

            $newChain = null;

            foreach ($chainMethodCalls as $call) {

                if ($this->isName($call->name, 'shouldReceive')) {
                    if (!isset($call->args[0]) || !$call->args[0]->value instanceof String_) {
                        throw new ShouldNotHappenException();
                    }

                    $newChain = $this->createMethodCall($call->var, $call->args[0]->value->value);
                }

                if ($this->isName($call->name, 'never')) {
                    $newChain =  $this->createMethodCall($newChain, 'shouldNotBeCalled');
                }
            }

            return $newChain;
        });
    }

    private function isMockVariable(Node $node): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        /** @var string $variableName */
        $variableName = $this->getName($node);

        return isset($this->mockVariableTypesByNames[$variableName]);
    }

    private function revealMockArguments(ClassMethod $node)
    {
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (!$node instanceof Node\Arg) {
                return null;
            }

            if (!$node->value instanceof Variable) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->getName($node->value);

            if (!isset($this->mockVariableTypesByNames[$variableName])) {
                return null;
            }

            return $this->createMethodCall($node->value, 'reveal');
        });
    }
}
