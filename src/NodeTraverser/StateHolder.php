<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

final class StateHolder
{
    /**
     * @var bool
     */
    private $isAfterTraverseCalled = false;

    public function setAfterTraverserIsCalled(): void
    {
        $this->isAfterTraverseCalled = true;
    }

    public function isAfterTraverseCalled(): bool
    {
        return $this->isAfterTraverseCalled;
    }
}

