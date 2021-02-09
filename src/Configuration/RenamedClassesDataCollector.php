<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

final class RenamedClassesDataCollector
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];

    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses(): array
    {
        return $this->oldToNewClasses;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function setOldToNewClasses(array $oldToNewClasses): void
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }
}
