<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Only adds package to require-dev section, if package is already in composer data, nothing happen
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\AddPackageToRequireDevTest
 */
final class AddPackageToRequireDev implements ComposerModifierInterface
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $version;

    public function __construct(string $packageName, string $version)
    {
        $this->packageName = $packageName;
        $this->version = $version;
    }

    public function modify(ComposerJson $composerJson): void
    {
        $composerJson->addRequiredDevPackage($this->packageName, $this->version);
    }
}
