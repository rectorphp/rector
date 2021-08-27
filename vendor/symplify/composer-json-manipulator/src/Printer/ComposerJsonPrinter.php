<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\ComposerJsonManipulator\Printer;

use RectorPrefix20210827\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use RectorPrefix20210827\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ComposerJsonPrinter
{
    /**
     * @var \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager
     */
    private $jsonFileManager;
    public function __construct(\RectorPrefix20210827\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager)
    {
        $this->jsonFileManager = $jsonFileManager;
    }
    public function printToString(\RectorPrefix20210827\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson) : string
    {
        return $this->jsonFileManager->encodeJsonToFileContent($composerJson->getJsonArray());
    }
    /**
     * @param string|\Symplify\SmartFileSystem\SmartFileInfo $targetFile
     */
    public function print(\RectorPrefix20210827\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson, $targetFile) : string
    {
        if (\is_string($targetFile)) {
            return $this->jsonFileManager->printComposerJsonToFilePath($composerJson, $targetFile);
        }
        return $this->jsonFileManager->printJsonToFileInfo($composerJson->getJsonArray(), $targetFile);
    }
}
