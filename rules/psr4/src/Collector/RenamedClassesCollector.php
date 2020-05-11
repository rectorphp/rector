<?php

declare(strict_types=1);

namespace Rector\PSR4\Collector;

final class RenamedClassesCollector
{
    /**
     * @var string[]
     */
    private $oldToNewClass = [];

    public function addClassRename(string $oldClass, string $newClass): void
    {
        $this->oldToNewClass[$oldClass] = $newClass;
    }

    /**
     * @return string[]
     */
    public function getOldToNewClasses(): array
    {
        return $this->oldToNewClass;
    }
}
