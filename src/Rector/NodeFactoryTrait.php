<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeFactoryTrait
{
    protected function createNull(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }

    protected function createFalse(): ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }

    protected function createTrue(): ConstFetch
    {
        return new ConstFetch(new Name('true'));
    }

    /**
     * @param mixed[] $arguments
     */
    protected function createFunction(string $name, array $arguments = []): FuncCall
    {
        return new FuncCall(new Name($name), $arguments);
    }
}
