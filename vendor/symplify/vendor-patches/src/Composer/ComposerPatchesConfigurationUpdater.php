<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\VendorPatches\Composer;

use RectorPrefix20220501\Symplify\Astral\Exception\ShouldNotHappenException;
use RectorPrefix20220501\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix20220501\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use RectorPrefix20220501\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use RectorPrefix20220501\Symplify\PackageBuilder\Yaml\ParametersMerger;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\VendorPatches\Tests\Composer\ComposerPatchesConfigurationUpdater\ComposerPatchesConfigurationUpdaterTest
 */
final class ComposerPatchesConfigurationUpdater
{
    /**
     * @var \Symplify\ComposerJsonManipulator\ComposerJsonFactory
     */
    private $composerJsonFactory;
    /**
     * @var \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager
     */
    private $jsonFileManager;
    /**
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    public function __construct(\RectorPrefix20220501\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory, \RectorPrefix20220501\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager, \RectorPrefix20220501\Symplify\PackageBuilder\Yaml\ParametersMerger $parametersMerger)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->jsonFileManager = $jsonFileManager;
        $this->parametersMerger = $parametersMerger;
    }
    /**
     * @param mixed[] $composerExtraPatches
     */
    public function updateComposerJson(string $composerJsonFilePath, array $composerExtraPatches) : \RectorPrefix20220501\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson
    {
        $extra = ['patches' => $composerExtraPatches];
        $composerJson = $this->composerJsonFactory->createFromFilePath($composerJsonFilePath);
        // merge "extra" section - deep merge is needed, so original patches are included
        $newExtra = $this->parametersMerger->merge($composerJson->getExtra(), $extra);
        $composerJson->setExtra($newExtra);
        return $composerJson;
    }
    /**
     * @param mixed[] $composerExtraPatches
     */
    public function updateComposerJsonAndPrint(string $composerJsonFilePath, array $composerExtraPatches) : void
    {
        $composerJson = $this->updateComposerJson($composerJsonFilePath, $composerExtraPatches);
        $fileInfo = $composerJson->getFileInfo();
        if (!$fileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            throw new \RectorPrefix20220501\Symplify\Astral\Exception\ShouldNotHappenException();
        }
        $this->jsonFileManager->printComposerJsonToFilePath($composerJson, $fileInfo->getRealPath());
    }
}
