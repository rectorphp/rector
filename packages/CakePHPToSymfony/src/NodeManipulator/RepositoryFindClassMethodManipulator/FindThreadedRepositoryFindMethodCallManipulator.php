<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;

final class FindThreadedRepositoryFindMethodCallManipulator extends AbstractRepositoryFindMethodCallManipulator implements RepositoryFindMethodCallManipulatorInterface
{
    public function getKeyName(): string
    {
        return 'threaded';
    }

    public function processMethodCall(MethodCall $methodCall, string $entityClass): MethodCall
    {
        $this->refactorToRepositoryMethod($methodCall, 'findBy');

        unset($methodCall->args[0]);

        $conditionsArray = $this->findConfigurationByKey($methodCall, 'conditions', $entityClass);
        if ($conditionsArray === null) {
            return $methodCall;
        }

        $methodCall->args = [new Arg($conditionsArray)];

        return $methodCall;
    }
}
