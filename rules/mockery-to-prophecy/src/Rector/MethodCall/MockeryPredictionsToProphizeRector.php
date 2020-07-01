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
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MockeryPredictionsToProphizeRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $mockVariableTypesByNames = [];

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

//        if ($node instanceof MethodCall) {
////            /** @var Scope $phpstanScope */
////            $phpstanScope = $node->getAttribute('PHPStan\Analyser\Scope');
////            if ($node->getAttribute('methodCallVariableName') !== null) {
////                $variableType = $phpstanScope->getVariableType($node->getAttribute('methodCallVariableName'));
////
////                if ($variableType->getClassName() === 'Mockery\MockInterface') {
//
////                }
////            }
//
//            //$node->getAttribute('origNode') => variable mock
//
//            if ($this->isName($node->name, 'shouldReceive')) {
//                /** @var String_ $val */
//                $val = $node->args[0]->value;
//
//                $methodCall = $this->createMethodCall(
//                    $node->var,
//                    (string) $val->value
//                );
//
//                /** @var MethodCall $nextMethod */
//                $nextMethod = $node->getAttribute('nextNode')->getAttribute('parentNode');
//
//                $this->removeNode($nextMethod);
//
//                if ($this->isName($node->getAttribute('nextNode'), 'never')) {
//                    return $this->createMethodCall($methodCall, 'shouldNotBeCalled');
//                }
//
//                return $methodCall;
//            }
//
////             if ($this->isName($node, 'never')) {
////                return $this->createMethodCall(
////                    $node->var,
////                    'shouldNotBeCalled'
////                );
////            }
//        }

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
            if (!$this->isMethodCallOnMockVariable($node)) {
                return null;
            }

            if ($node instanceof MethodCall) {
                /** @var $node MethodCall */
                if ($this->isName($node->name, 'shouldReceive')) {
                    if (!isset($node->args[0]) || !$node->args[0]->value instanceof String_) {
                        throw new ShouldNotHappenException();
                    }

                    $methodCall = new MethodCall($node->var, $node->args[0]->value->value);

                    $nextCall = $node->getAttribute('nextNode');
                    if ($nextCall instanceof Node\Identifier) {
                        $nextParent = $nextCall->getAttribute('parentNode');
                        if ($nextParent instanceof MethodCall && $this->isName($nextParent->name, 'never')) {
                           return $this->createMethodCall($methodCall, 'shouldNotBeCalled');
                        }
                    }

                    return $methodCall;
                }
            }
        });
    }

    private function isMethodCallOnMockVariable(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        /** @var string $variableName */
        $variableName = $this->getName($node->var);

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
