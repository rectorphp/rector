<?php

declare (strict_types=1);
namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\ValueObject\RenamePackage;
use RectorPrefix20220531\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\Rector\RenamePackageComposerRector\RenamePackageComposerRectorTest
 */
final class RenamePackageComposerRector implements \Rector\Composer\Contract\Rector\ComposerRectorInterface
{
    /**
     * @var RenamePackage[]
     */
    private $renamePackages = [];
    public function refactor(\RectorPrefix20220531\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        foreach ($this->renamePackages as $renamePackage) {
            if ($composerJson->hasRequiredPackage($renamePackage->getOldPackageName())) {
                $version = $composerJson->getRequire()[$renamePackage->getOldPackageName()];
                $composerJson->replacePackage($renamePackage->getOldPackageName(), $renamePackage->getNewPackageName(), $version);
            }
            if ($composerJson->hasRequiredDevPackage($renamePackage->getOldPackageName())) {
                $version = $composerJson->getRequireDev()[$renamePackage->getOldPackageName()];
                $composerJson->replacePackage($renamePackage->getOldPackageName(), $renamePackage->getNewPackageName(), $version);
            }
        }
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change package name in `composer.json`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
    "require": {
        "rector/rector": "dev-main"
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "require": {
        "rector/rector-src": "dev-main"
    }
}
CODE_SAMPLE
, [new \Rector\Composer\ValueObject\RenamePackage('rector/rector', 'rector/rector-src')])]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Composer\ValueObject\RenamePackage::class);
        $this->renamePackages = $configuration;
    }
}
