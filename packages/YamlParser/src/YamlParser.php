<?php declare(strict_types=1);

namespace Rector\YamlParser;

use Symfony\Component\Yaml\Yaml;

/**
 * Inspire:
 *
 * - https://github.com/klausi/yaml_comments
 * - https://github.com/mulesoft-labs/yaml-ast-parser
 */
final class YamlParser
{
    public function parseFile(string $file): array
    {
        $yaml = Yaml::parse(file_get_contents($file));

        return $yaml;
    }
}
