<?php declare(strict_types=1);

namespace Rector\YamlParser;

use Rector\FileSystem\FileGuard;
use Symfony\Component\Yaml\Yaml;

final class YamlParser
{
    /**
     * @return mixed[]
     */
    public function parseFile(string $file): array
    {
        FileGuard::ensureFileExists($file, __METHOD__);

        return Yaml::parse(file_get_contents($file));
    }

    /**
     * @param mixed[] $data
     */
    public function getStringFromData(array $data): string
    {
        return Yaml::dump($data);
    }
}
