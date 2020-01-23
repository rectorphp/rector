<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use PhpParser\Node\Expr\MethodCall;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;

final class FindAllRepositoryFindMethodCallManipulator extends AbstractRepositoryFindMethodCallManipulator implements RepositoryFindMethodCallManipulatorInterface
{
    public function getKeyName(): string
    {
        return 'all';
    }

    public function processMethodCall(MethodCall $methodCall): MethodCall
    {
        $this->refactorToRepositoryMethod($methodCall, 'findAll');

        $methodCall->args = [];

        return $methodCall;
    }
}
