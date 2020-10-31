<?php

declare(strict_types=1);

namespace Rector\Testing\Contract;

interface RectorInterfaceAwareInterface
{
    /**
     * Return interface type that extends @see \Rector\Core\Contract\Rector\RectorInterface;
     */
    public function getRectorInterface(): string;
}
