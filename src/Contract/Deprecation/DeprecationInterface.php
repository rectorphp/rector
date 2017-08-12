<?php declare(strict_types=1);

namespace Rector\Contract\Deprecation;

use Rector\Deprecation\SetNames;

interface DeprecationInterface
{
    /**
     * A project that is related to this.
     * E.g "Nette", "Symfony"
     * Use constants from @see SetNames, if possible.
     */
    public function getSetName(): string;

    /**
     * Version this deprecations is active since.
     * E.g. 2.3.
     */
    public function sinceVersion(): float;
}
