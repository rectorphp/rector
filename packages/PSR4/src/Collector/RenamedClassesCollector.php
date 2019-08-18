<?php declare(strict_types=1);

namespace Rector\PSR4\Collector;

final class RenamedClassesCollector
{
    /**
     * @var string[]
     */
    private $renamedClasses = [];

    public function addClassRename(string $oldClass, string $newClass): void
    {
        $this->renamedClasses[$oldClass] = $newClass;
    }

    /**
     * @return string[]
     */
    public function getRenamedClasses(): array
    {
        return $this->renamedClasses;
    }
}
