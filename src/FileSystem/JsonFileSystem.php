<?php

declare (strict_types=1);
namespace Rector\FileSystem;

use RectorPrefix202403\Nette\Utils\FileSystem;
use RectorPrefix202403\Nette\Utils\Json;
final class JsonFileSystem
{
    /**
     * @return array<string, mixed>
     */
    public static function readFilePath(string $filePath) : array
    {
        $fileContents = FileSystem::read($filePath);
        return Json::decode($fileContents, Json::FORCE_ARRAY);
    }
    /**
     * @param array<string, mixed> $data
     */
    public static function writeFile(string $filePath, array $data) : void
    {
        $json = Json::encode($data, Json::PRETTY);
        FileSystem::write($filePath, $json, null);
    }
}
