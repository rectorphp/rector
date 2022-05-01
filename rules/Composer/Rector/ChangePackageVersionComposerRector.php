<?php

declare (strict_types=1);
namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\Guard\VersionGuard;
use Rector\Composer\ValueObject\PackageAndVersion;
use RectorPrefix20220501\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\Rector\ChangePackageVersionComposerRector\ChangePackageVersionComposerRectorTest
 */
final class ChangePackageVersionComposerRector implements \Rector\Composer\Contract\Rector\ComposerRectorInterface
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
    public function __construct(\Rector\Composer\Guard\VersionGuard $versionGuard)
    {
        $this->versionGuard = $versionGuard;
    }
    public function refactor(\RectorPrefix20220501\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        foreach ($this->packagesAndVersions as $packageAndVersion) {
            $composerJson->changePackageVersion($packageAndVersion->getPackageName(), $packageAndVersion->getVersion());
        }
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change package version `composer.json`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
    "require": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "require": {
        "symfony/console": "^4.4"
    }
}
CODE_SAMPLE
, [new \Rector\Composer\ValueObject\PackageAndVersion('symfony/console', '^4.4')])]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Composer\ValueObject\PackageAndVersion::class);
        $this->versionGuard->validate($configuration);
        $this->packagesAndVersions = $configuration;
    }
}
