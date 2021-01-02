<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerChanger;

final class ChangePackageVersion implements ComposerChangerInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $targetVersion;

    public function __construct(string $packageName, string $targetVersion)
    {
        $this->packageName = $packageName;
        $this->targetVersion = $targetVersion;
    }

    public function process(array $composerData): array
    {
        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section][$this->packageName])) {
                $composerData[$section][$this->packageName] = $this->targetVersion;
            }
        }
        return $composerData;
    }
}
