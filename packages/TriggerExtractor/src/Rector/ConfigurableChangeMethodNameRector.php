<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Rector;

use Rector\Rector\AbstractChangeMethodNameRector;

final class ConfigurableChangeMethodNameRector extends AbstractChangeMethodNameRector
{
    /**
     * @var string[][]
     */
    private $perClassOldToNewMethod;

    public function setPerClassOldToNewMethods(array $perClassOldToNewMethod): void
    {
        $this->perClassOldToNewMethod = $perClassOldToNewMethod;
    }

    public function getSetName(): string
    {
        return 'dynamic';
    }

    public function sinceVersion(): float
    {
        return 0.0;
    }

    /**
     * @return string[][] { class => [ oldMethod => newMethod ] }
     */
    protected function getPerClassOldToNewMethods(): array
    {
        return $this->perClassOldToNewMethod;
    }
}
