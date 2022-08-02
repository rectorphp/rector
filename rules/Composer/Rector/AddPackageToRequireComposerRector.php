<?php

declare (strict_types=1);
namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\Guard\VersionGuard;
use Rector\Composer\ValueObject\PackageAndVersion;
use RectorPrefix202208\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\Rector\AddPackageToRequireComposerRector\AddPackageToRequireComposerRectorTest
 */
final class AddPackageToRequireComposerRector implements ComposerRectorInterface
{
    /**
     * @var PackageAndVersion[]
     */
    private $packagesAndVersions = [];
    /**
     * @readonly
     * @var \Rector\Composer\Guard\VersionGuard
     */
    private $versionGuard;
    public function __construct(VersionGuard $versionGuard)
    {
        $this->versionGuard = $versionGuard;
    }
    public function refactor(ComposerJson $composerJson) : void
    {
        foreach ($this->packagesAndVersions as $packageAndVersion) {
            $composerJson->addRequiredPackage($packageAndVersion->getPackageName(), $packageAndVersion->getVersion());
        }
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add package to "require" in `composer.json`', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "require": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
, [new PackageAndVersion('symfony/console', '^3.4')])]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, PackageAndVersion::class);
        $this->versionGuard->validate($configuration);
        $this->packagesAndVersions = $configuration;
    }
}
