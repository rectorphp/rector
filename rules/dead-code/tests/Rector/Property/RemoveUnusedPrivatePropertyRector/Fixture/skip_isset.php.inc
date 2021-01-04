<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipIsset
{
    /** @var string */
    private $packageName;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function modify(array $composerData): array
    {
        if (isset($composerData['require'][$this->packageName])) {
            return [];
        }

        return $composerData;
    }
}