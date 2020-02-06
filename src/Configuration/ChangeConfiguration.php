<?php

declare(strict_types=1);

namespace Rector\Configuration;

final class ChangeConfiguration
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @param string[] $oldToNewClasses
     */
    public function setOldToNewClasses(array $oldToNewClasses): void
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }

    /**
     * @return string[]
     */
    public function getOldToNewClasses(): array
    {
        return $this->oldToNewClasses;
    }
}
