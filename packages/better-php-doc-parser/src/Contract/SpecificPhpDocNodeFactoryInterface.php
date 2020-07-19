<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

interface SpecificPhpDocNodeFactoryInterface extends PhpDocNodeFactoryInterface
{
    /**
     * @return string[]
     */
    public function getClasses(): array;
}
