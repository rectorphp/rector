<?php

declare (strict_types=1);
namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\Guard\VersionGuard;
use Rector\Composer\ValueObject\PackageAndVersion;
use RectorPrefix20211020\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\Rector\AddPackageToRequireDevComposerRector\AddPackageToRequireDevComposerRectorTest
 */
final class AddPackageToRequireDevComposerRector implements \Rector\Composer\Contract\Rector\ComposerRectorInterface
{
    /**
     * @var string
     */
    public const PACKAGES_AND_VERSIONS = 'packages_and_version';
    /**
     * @var PackageAndVersion[]
     */
    private $packageAndVersions = [];
    /**
     * @var \Rector\Composer\Guard\VersionGuard
     */
    private $versionGuard;
    public function __construct(\Rector\Composer\Guard\VersionGuard $versionGuard)
    {
        $this->versionGuard = $versionGuard;
    }
    public function refactor(\RectorPrefix20211020\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        foreach ($this->packageAndVersions as $packageAndVersion) {
            $composerJson->addRequiredDevPackage($packageAndVersion->getPackageName(), $packageAndVersion->getVersion());
        }
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add package to "require-dev" in `composer.json`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "require-dev": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
, [self::PACKAGES_AND_VERSIONS => [new \Rector\Composer\ValueObject\PackageAndVersion('symfony/console', '^3.4')]])]);
    }
    /**
     * @param array<string, PackageAndVersion[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $packagesAndVersions = $configuration[self::PACKAGES_AND_VERSIONS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($packagesAndVersions, \Rector\Composer\ValueObject\PackageAndVersion::class);
        $this->versionGuard->validate($packagesAndVersions);
        $this->packageAndVersions = $packagesAndVersions;
    }
}
