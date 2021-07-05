<?php

declare (strict_types=1);
namespace RectorPrefix20210705\Symplify\VendorPatches\Composer;

use RectorPrefix20210705\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix20210705\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
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
    public function __construct(\RectorPrefix20210705\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory, \RectorPrefix20210705\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->jsonFileManager = $jsonFileManager;
    }
    /**
     * @param mixed[] $composerExtraPatches
     */
    public function updateComposerJson(array $composerExtraPatches) : void
    {
        $extra = ['patches' => $composerExtraPatches];
        $composerJsonFilePath = \getcwd() . '/composer.json';
        $composerJson = $this->composerJsonFactory->createFromFilePath($composerJsonFilePath);
        // merge "extra" section
        $newExtra = \array_merge($composerJson->getExtra(), $extra);
        $composerJson->setExtra($newExtra);
        $this->jsonFileManager->printComposerJsonToFilePath($composerJson, $composerJsonFilePath);
    }
}
