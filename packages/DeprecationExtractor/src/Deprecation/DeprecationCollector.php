<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;

final class DeprecationCollector
{
    /**
     * @var DeprecationInterface[]
     */
    private $deprecations = [];

    public function addDeprecation(DeprecationInterface $deprecation): void
    {
        $this->deprecations[] = $deprecation;
    }

    /**
     * @return DeprecationInterface[]
     */
    public function getDeprecations(): array
    {
        return $this->deprecations;
    }
}
