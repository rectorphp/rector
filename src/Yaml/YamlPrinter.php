<?php

declare(strict_types=1);

namespace Rector\Yaml;

use Nette\Utils\FileSystem;
use Symfony\Component\Yaml\Yaml;

final class YamlPrinter
{
    public function printYamlToString(array $yaml): string
    {
        return Yaml::dump($yaml, 10, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    public function printYamlToFile(array $yaml, string $targetFile): void
    {
        $yamlContent = $this->printYamlToString($yaml);
        FileSystem::write($targetFile, $yamlContent);
    }
}
