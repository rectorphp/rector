<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\ComposerJsonManipulator\FileSystem;

use RectorPrefix20220609\Nette\Utils\Json;
use RectorPrefix20220609\Symplify\ComposerJsonManipulator\Json\JsonCleaner;
use RectorPrefix20220609\Symplify\ComposerJsonManipulator\Json\JsonInliner;
use RectorPrefix20220609\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use RectorPrefix20220609\Symplify\PackageBuilder\Configuration\StaticEolConfiguration;
use RectorPrefix20220609\Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220609\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @see \Symplify\MonorepoBuilder\Tests\FileSystem\JsonFileManager\JsonFileManagerTest
 */
final class JsonFileManager
{
    /**
     * @var array<string, mixed[]>
     */
    private $cachedJSONFiles = [];
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Symplify\ComposerJsonManipulator\Json\JsonCleaner
     */
    private $jsonCleaner;
    /**
     * @var \Symplify\ComposerJsonManipulator\Json\JsonInliner
     */
    private $jsonInliner;
    public function __construct(SmartFileSystem $smartFileSystem, JsonCleaner $jsonCleaner, JsonInliner $jsonInliner)
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->jsonCleaner = $jsonCleaner;
        $this->jsonInliner = $jsonInliner;
    }
    /**
     * @return mixed[]
     */
    public function loadFromFileInfo(SmartFileInfo $smartFileInfo) : array
    {
        $realPath = $smartFileInfo->getRealPath();
        if (!isset($this->cachedJSONFiles[$realPath])) {
            $this->cachedJSONFiles[$realPath] = Json::decode($smartFileInfo->getContents(), Json::FORCE_ARRAY);
        }
        return $this->cachedJSONFiles[$realPath];
    }
    /**
     * @return array<string, mixed>
     */
    public function loadFromFilePath(string $filePath) : array
    {
        $fileContent = $this->smartFileSystem->readFile($filePath);
        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }
    /**
     * @param mixed[] $json
     */
    public function printJsonToFileInfo(array $json, SmartFileInfo $smartFileInfo) : string
    {
        $jsonString = $this->encodeJsonToFileContent($json);
        $this->smartFileSystem->dumpFile($smartFileInfo->getPathname(), $jsonString);
        $realPath = $smartFileInfo->getRealPath();
        unset($this->cachedJSONFiles[$realPath]);
        return $jsonString;
    }
    public function printComposerJsonToFilePath(ComposerJson $composerJson, string $filePath) : string
    {
        $jsonString = $this->encodeJsonToFileContent($composerJson->getJsonArray());
        $this->smartFileSystem->dumpFile($filePath, $jsonString);
        return $jsonString;
    }
    /**
     * @param mixed[] $json
     */
    public function encodeJsonToFileContent(array $json) : string
    {
        // Empty arrays may lead to bad encoding since we can't be sure whether they need to be arrays or objects.
        $json = $this->jsonCleaner->removeEmptyKeysFromJsonArray($json);
        $jsonContent = Json::encode($json, Json::PRETTY) . StaticEolConfiguration::getEolChar();
        return $this->jsonInliner->inlineSections($jsonContent);
    }
}
