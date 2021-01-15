<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\ChangePackageVersionTest
 */
final class ChangePackageVersion
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $targetVersion;

    public function __construct(string $packageName, string $targetVersion)
    {
        $this->packageName = $packageName;
        $this->targetVersion = $targetVersion;
    }

    public function refactor(ComposerJson $composerJson): void
    {
        $composerJson->changePackageVersion($this->packageName, $this->targetVersion);
    }
}
