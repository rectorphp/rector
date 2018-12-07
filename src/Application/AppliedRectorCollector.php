<?php declare(strict_types=1);

namespace Rector\Application;

final class AppliedRectorCollector
{
    /**
     * @var string[]
     */
    private $rectorClasses = [];

    public function addRectorClass(string $rectorClass): void
    {
        $this->rectorClasses[] = $rectorClass;
    }

    public function reset(): void
    {
        $this->rectorClasses = [];
    }

    /**
     * @return string[]
     */
    public function getRectorClasses(): array
    {
        return array_unique($this->rectorClasses);
    }
}
