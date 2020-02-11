<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;

final class FindFirstRepositoryFindMethodCallManipulator extends AbstractRepositoryFindMethodCallManipulator implements RepositoryFindMethodCallManipulatorInterface
{
    public function getKeyName(): string
    {
        return 'first';
    }

    public function processMethodCall(MethodCall $methodCall, string $entityClass): MethodCall
    {
        $this->refactorToRepositoryMethod($methodCall, 'findOneBy');

        unset($methodCall->args[0]);
        if (! isset($methodCall->args[1])) {
            return $methodCall;
        }

        $firstArgument = $methodCall->args[1]->value;
        if (! $firstArgument instanceof Array_) {
            return $methodCall;
        }

        $methodCall->args = $this->createFindOneByArgs($entityClass, $methodCall);

        return $methodCall;
    }

    /**
     * @return Arg[]
     */
    private function createFindOneByArgs(string $entityClass, MethodCall $methodCall): array
    {
        $args = [];

        $conditionsArray = $this->findConfigurationByKey($methodCall, 'conditions', $entityClass);
        if ($conditionsArray !== null) {
            $args[] = new Arg($conditionsArray);
        }

        $orderArray = $this->findConfigurationByKey($methodCall, 'order', $entityClass);
        if ($orderArray !== null) {
            if (count($args) === 0) {
                $args[] = new Arg($this->createNull());
            }

            assert(isset($orderArray->items[0]));
            $args[] = new Arg($orderArray->items[0]);
        }

        return $args;
    }

    private function createNull(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }
}
