<?php

declare (strict_types=1);
namespace Rector\Skipper\FileSystem;

final class PathNormalizer
{
    public static function normalize(string $path) : string
    {
        return \str_replace('\\', '/', $path);
    }
}
