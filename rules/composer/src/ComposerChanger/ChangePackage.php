<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerChanger;

final class ChangePackage implements ComposerChangerInterface
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

    public function process(array $composerData): array
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
