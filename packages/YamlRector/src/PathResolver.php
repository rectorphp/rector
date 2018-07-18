<?php declare(strict_types=1);

namespace Rector\YamlRector;

use Nette\Utils\Strings;

final class PathResolver
{
    /**
     * Split by " > " or ">"
     *
     * @return string[]
     */
    public function splitPathToParts(string $path): array
    {
        return Strings::split($path, '#[\s+]?>[\s+]?#');
    }
}
