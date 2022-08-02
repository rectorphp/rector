<?php

declare (strict_types=1);
namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\Guard\VersionGuard;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use RectorPrefix202208\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\Rector\ReplacePackageAndVersionComposerRector\ReplacePackageAndVersionComposerRectorTest
 */
final class ReplacePackageAndVersionComposerRector implements ComposerRectorInterface
{
    /**
     * @var ReplacePackageAndVersion[]
     */
    private $replacePackagesAndVersions = [];
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
        foreach ($this->replacePackagesAndVersions as $replacePackageAndVersion) {
            $composerJson->replacePackage($replacePackageAndVersion->getOldPackageName(), $replacePackageAndVersion->getNewPackageName(), $replacePackageAndVersion->getVersion());
        }
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change package name and version `composer.json`', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
    "require-dev": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "require-dev": {
        "symfony/http-kernel": "^4.4"
    }
}
CODE_SAMPLE
, [new ReplacePackageAndVersion('symfony/console', 'symfony/http-kernel', '^4.4')])]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ReplacePackageAndVersion::class);
        $this->versionGuard->validate($configuration);
        $this->replacePackagesAndVersions = $configuration;
    }
}
