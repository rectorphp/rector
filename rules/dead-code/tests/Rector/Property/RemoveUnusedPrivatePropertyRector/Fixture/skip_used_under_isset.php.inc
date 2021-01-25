<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Rector\Composer\Rector\ComposerModifier;
use Rector\Composer\Contract\Rector\ComposerRectorInterface;

final class SkipUsedUnderIsset
{
    /** @var string */
    private $oldPackageName;

    /** @var string */
    private $newPackageName;

    /** @var string */
    private $targetVersion;

    public function __construct(string $oldPackageName, string $newPackageName, string $targetVersion)
    {
        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
        $this->targetVersion = $targetVersion;
    }

    public function modify(array $composerData): array
    {
        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section][$this->oldPackageName])) {
                unset($composerData[$section][$this->oldPackageName]);
                $composerData[$section][$this->newPackageName] = $this->targetVersion;
            }
        }

        return $composerData;
    }
}
