<?php

declare (strict_types=1);
namespace Rector\Skipper\FileSystem;

/**
 * @see \Rector\Tests\Skipper\FileSystem\FnMatchPathNormalizerTest
 */
final class FnMatchPathNormalizer
{
    public function normalizeForFnmatch(string $path) : string
    {
        if (\substr_compare($path, '*', -\strlen('*')) === 0 || \strncmp($path, '*', \strlen('*')) === 0) {
            return '*' . \trim($path, '*') . '*';
        }
        if (\strpos($path, '..') !== \false) {
            /** @var string|false $realPath */
            $realPath = \realpath($path);
            if ($realPath === \false) {
                return '';
            }
            return \Rector\Skipper\FileSystem\PathNormalizer::normalize($realPath);
        }
        return $path;
    }
}
