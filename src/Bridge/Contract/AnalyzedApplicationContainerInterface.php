<?php declare(strict_types=1);

namespace Rector\Bridge\Contract;

use PHPStan\Type\Type;

interface AnalyzedApplicationContainerInterface
{
    public function getTypeForName(string $name): Type;

    public function hasService(string $name): bool;

    /**
     * @return object
     */
    public function getService(string $name);
}
