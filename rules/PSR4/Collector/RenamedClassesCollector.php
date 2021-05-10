<?php

declare (strict_types=1);
namespace Rector\PSR4\Collector;

use Rector\Core\Configuration\RenamedClassesDataCollector;
/**
 * @deprecated Merge with RenamedClassesDataCollector
 */
final class RenamedClassesCollector
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClass = [];
    /**
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(\Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function addClassRename(string $oldClass, string $newClass) : void
    {
        $this->oldToNewClass[$oldClass] = $newClass;
    }
    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses() : array
    {
        return \array_merge($this->oldToNewClass, $this->renamedClassesDataCollector->getOldToNewClasses());
    }
}
