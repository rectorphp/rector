<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Symplify\Skipper\FileSystem;

use RectorPrefix20220418\Nette\Utils\Strings;
/**
 * @see \Symplify\Skipper\Tests\FileSystem\FnMatchPathNormalizerTest
 */
final class FnMatchPathNormalizer
{
    /**
     * @var string
     * @see https://regex101.com/r/ZB2dFV/2
     */
    private const ONLY_ENDS_WITH_ASTERISK_REGEX = '#^[^*](.*?)\\*$#';
    /**
     * @var string
     * @see https://regex101.com/r/aVUDjM/2
     */
    private const ONLY_STARTS_WITH_ASTERISK_REGEX = '#^\\*(.*?)[^*]$#';
    public function normalizeForFnmatch(string $path) : string
    {
        // ends with *
        if (\RectorPrefix20220418\Nette\Utils\Strings::match($path, self::ONLY_ENDS_WITH_ASTERISK_REGEX)) {
            return '*' . $path;
        }
        // starts with *
        if (\RectorPrefix20220418\Nette\Utils\Strings::match($path, self::ONLY_STARTS_WITH_ASTERISK_REGEX)) {
            return $path . '*';
        }
        if (\strpos($path, '..') !== \false) {
            $path = \realpath($path);
            if ($path === \false) {
                return '';
            }
        }
        return $path;
    }
}
