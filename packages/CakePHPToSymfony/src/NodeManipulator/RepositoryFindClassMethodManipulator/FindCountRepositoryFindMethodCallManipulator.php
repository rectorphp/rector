<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;

final class FindCountRepositoryFindMethodCallManipulator extends AbstractRepositoryFindMethodCallManipulator implements RepositoryFindMethodCallManipulatorInterface
{
    public function getKeyName(): string
    {
        return 'count';
    }

    public function processMethodCall(MethodCall $methodCall, string $entityClass): MethodCall
    {
        $this->refactorToRepositoryMethod($methodCall, 'count');

        $conditionsArray = $this->findConfigurationByKey($methodCall, 'conditions', $entityClass);

        if ($conditionsArray === null) {
            return $methodCall;
        }

        $methodCall->args = [new Arg($conditionsArray)];

        return $methodCall;
    }
}
