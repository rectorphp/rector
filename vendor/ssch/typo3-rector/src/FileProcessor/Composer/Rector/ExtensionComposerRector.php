<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Composer\Rector;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ExtensionArchitecture/ComposerJson/Index.html#extra
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Composer\Rector\ExtensionComposerRector\ExtensionComposerRectorTest
 */
final class ExtensionComposerRector implements \Rector\Composer\Contract\Rector\ComposerRectorInterface
{
    /**
     * @var string
     */
    public const TYPO3_VERSION_CONSTRAINT = 'typo3_version_constraint';
    /**
     * @var string
     */
    private $defaultTypo3VersionConstraint = '';
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    public function refactor(\RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        if ('typo3-cms-extension' !== $composerJson->getType()) {
            return;
        }
        if ('' !== $this->defaultTypo3VersionConstraint) {
            $composerJson->addRequiredPackage('typo3/cms-core', $this->defaultTypo3VersionConstraint);
            $composerJson->changePackageVersion('typo3/cms-core', $this->defaultTypo3VersionConstraint);
        }
        $this->addExtensionKey($composerJson);
        $this->addDescription($composerJson);
        $this->addLicense($composerJson);
        $this->fixPackageName($composerJson);
        $file = $this->currentFileProvider->getFile();
        if ($file instanceof \Rector\Core\ValueObject\Application\File) {
            // TODO: How to add correct line number here?
            $file->addRectorClassWithLine(new \Rector\ChangesReporting\ValueObject\RectorWithLineChange($this, 0));
        }
    }
    /**
     * @param array<string, string> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->defaultTypo3VersionConstraint = $configuration[self::TYPO3_VERSION_CONSTRAINT] ?? '*';
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add extra extension_key in `composer.json` and add option default constraint', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
{
    "require": {
      "typo3/cms-core": "^9.5"
   },
    "extra": {}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
   "require": {
      "typo3/cms-core": "^10.4"
   },
   "extra": {
      "typo3/cms": {
         "extension-key": "my_extension"
      }
   }
}
CODE_SAMPLE
, [self::TYPO3_VERSION_CONSTRAINT => '^10.4'])]);
    }
    private function addExtensionKey(\RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        $extra = $composerJson->getExtra();
        if (isset($extra['typo3/cms']['extension-key'])) {
            return;
        }
        $fileInfo = $composerJson->getFileInfo();
        if (!$fileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return;
        }
        $extra['typo3/cms']['extension-key'] = \basename(\dirname($fileInfo->getRealPath()));
        $composerJson->setExtra($extra);
    }
    private function addDescription(\RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        $description = $composerJson->getDescription();
        if ('' !== $description && null !== $description) {
            return;
        }
        $composerJson->setDescription('Add description...');
    }
    private function addLicense(\RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        $license = $composerJson->getLicense();
        if ('' !== $license && null !== $license && [] !== $license) {
            return;
        }
        $composerJson->setLicense('GPL-2.0-or-later');
    }
    private function fixPackageName(\RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : void
    {
        $name = $composerJson->getName();
        if ('' === $name) {
            return;
        }
        if (null === $name) {
            return;
        }
        if (\strpos($name, '_') === \false) {
            return;
        }
        $composerJson->setName(\str_replace('_', '-', $name));
    }
}
