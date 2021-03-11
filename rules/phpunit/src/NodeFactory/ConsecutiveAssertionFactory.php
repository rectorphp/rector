<?php
declare(strict_types=1);


namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;

final class ConsecutiveAssertionFactory
{
    /**
     * @param Arg[] $args
     */
    public function createWillReturnOnConsecutiveCalls(Expr $expr, array $args): MethodCall
    {
        return $this->createMethodCall($expr, 'willReturnOnConsecutiveCalls', $args);
    }

    /**
     * @param Arg[] $args
     */
    public function createMethod(Expr $expr, array $args): MethodCall
    {
        return $this->createMethodCall($expr, 'method', $args);
    }

    /**
     * @param Arg[] $args
     */
    public function createWithConsecutive(Expr $expr, array $args): MethodCall
    {
        return $this->createMethodCall($expr, 'withConsecutive', $args);
    }

    /**
     * @param Arg[] $args
     */
    private function createMethodCall(Expr $expr, string $name, array $args): MethodCall
    {
        return new MethodCall(
            $expr,
            new Name($name),
            $args
        );
    }
}
