<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipUnset
{
    /** @var string */
    private $packageName;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function modify(array $composerData): array
    {
        unset($composerData['require'][$this->packageName]);
        return $composerData;
    }
}