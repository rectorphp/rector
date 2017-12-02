<?php declare(strict_types=1);

namespace Rector\YamlParser;

use Klausi\YamlComments\ParseResult;
use Klausi\YamlComments\YamlComments;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Yaml\Yaml;

final class YamlParser
{
    public function parseFile(string $file): ParseResult
    {
        FileGuard::ensureFileExists($file, __METHOD__);

        return YamlComments::parse(file_get_contents($file));
    }

    /**
     * @param mixed[] $data
     */
    public function getStringFromData(array $data): string
    {
        return Yaml::dump($data);
    }
}
