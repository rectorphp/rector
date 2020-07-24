<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\FileSystem;

use Nette\Utils\Json;
use Symplify\SmartFileSystem\SmartFileSystem;

final class JsonFileSystem
{
    /**
     * @var JsonStringFormatter
     */
    private $jsonStringFormatter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(JsonStringFormatter $jsonStringFormatter, SmartFileSystem $smartFileSystem)
    {
        $this->jsonStringFormatter = $jsonStringFormatter;
        $this->smartFileSystem = $smartFileSystem;
    }

    /**
     * @return mixed[]
     */
    public function loadFileToJson(string $filePath): array
    {
        $fileContent = $this->smartFileSystem->readFile($filePath);
        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @param mixed[] $json
     */
    public function saveJsonToFile(string $filePath, array $json): void
    {
        $content = Json::encode($json, Json::PRETTY);
        $content = $this->jsonStringFormatter->inlineSections($content, ['keywords', 'bin']);
        $content = $this->jsonStringFormatter->inlineAuthors($content);

        // make sure there is newline in the end
        $content = trim($content) . PHP_EOL;

        $this->smartFileSystem->dumpFile($filePath, $content);
    }
}
