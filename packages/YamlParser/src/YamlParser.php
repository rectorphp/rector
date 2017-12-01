<?php declare(strict_types=1);

namespace Rector\YamlParser;

use Klausi\YamlComments\ParseResult;
use Klausi\YamlComments\YamlComments;

final class YamlParser
{
    public function parseFile(string $file): ParseResult
    {
        return YamlComments::parse(file_get_contents($file));
    }
}
