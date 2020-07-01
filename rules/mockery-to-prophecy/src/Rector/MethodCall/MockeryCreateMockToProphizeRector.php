<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MockeryToProphecy\MockeryUtils;

final class MockeryCreateMockToProphizeRector extends AbstractPHPUnitRector
{
    use MockeryUtils;

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$this->isInTestClass($node)) {
            return null;
        }

        if ($node instanceof MethodCall) {
            foreach ($node->args as $position => $arg) {
                /** @var Node\Expr\Variable $value */
                $value = $arg->value;
                if ($value instanceof Node\Expr\Variable) {
                    /** @var Scope $scope */
                    $scope = $value->getAttribute('PHPStan\Analyser\Scope');
                    /** @var ObjectType */
                    $type = $scope->getVariableType($value->name);

                    if ($type->getClassName() === 'Mockery\MockInterface') {
                        $node->args[$position] = $this->createArg($this->createMethodCall($arg->value, 'reveal'));
                    }
                }
            }
        }

        if ($this->isCallToMockery($node) && $node->name->toString() === 'mock') {
            if ($node->getAttribute('parentNode') instanceof Node\Arg) {
                return $this->createMethodCall($this->prophesizeCreateMock($node), 'reveal');
            }

            return $this->prophesizeCreateMock($node);
        }

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes mockery mock creation to Prophesize',
            [
                new CodeSample(
                    'aaaa',
                    'bbb'
                )
            ]
        );
    }

    /**
     * @param $node
     * @return MethodCall
     */
    private function prophesizeCreateMock($node): MethodCall
    {
        return $this->createLocalMethodCall('prophesize', [$node->args[0]]);
    }
}
