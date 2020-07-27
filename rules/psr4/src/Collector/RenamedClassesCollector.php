<?php

declare(strict_types=1);

namespace Rector\PSR4\Collector;

use Rector\Core\Configuration\ChangeConfiguration;

final class RenamedClassesCollector
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClass = [];

    /**
     * @var ChangeConfiguration
     */
    private $changeConfiguration;

    public function __construct(ChangeConfiguration $changeConfiguration)
    {
        $this->changeConfiguration = $changeConfiguration;
    }

    public function addClassRename(string $oldClass, string $newClass): void
    {
        $this->oldToNewClass[$oldClass] = $newClass;
    }

    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses(): array
    {
        return array_merge($this->oldToNewClass, $this->changeConfiguration->getOldToNewClasses());
    }
}
