<?php declare(strict_types=1);

namespace Rector\Bridge\Contract;

interface AnalyzedApplicationContainerInterface
{
    public function getTypeForName(string $name): ?string;

    public function hasService(string $name): bool;

    /**
     * @return object
     */
    public function getService(string $name);
}
