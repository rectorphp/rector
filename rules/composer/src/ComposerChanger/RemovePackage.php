<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerChanger;

final class RemovePackage implements ComposerChangerInterface
{
    /** @var string */
    private $packageName;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function process(array $composerData): array
    {
        foreach (['require', 'require-dev'] as $section) {
            unset($composerData[$section][$this->packageName]);
            if (empty($composerData[$section])) {
                unset($composerData[$section]);
            }
        }
        return $composerData;
    }
}
