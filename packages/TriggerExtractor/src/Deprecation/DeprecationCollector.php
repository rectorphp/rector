<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Deprecation;

final class DeprecationCollector
{
    /**
     * @var mixed[]
     */
    private $deprecations = [];

    public function addDeprecation(string $deprecation): void
    {
        $this->deprecations[] = $deprecation;
    }

    /**
     * @return mixed[]
     */
    public function getDeprecations(): array
    {
        return $this->deprecations;
    }
}
