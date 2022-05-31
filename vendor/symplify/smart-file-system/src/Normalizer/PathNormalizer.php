<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Symplify\SmartFileSystem\Normalizer;

use RectorPrefix20220531\Nette\Utils\Strings;
/**
 * @api
 *
 * Used from
 * https://github.com/phpstan/phpstan-src/blob/02425e61aa48f0668b4efb3e73d52ad544048f65/src/File/FileHelper.php#L40,
 * with custom modifications
 *
 * @see \Symplify\SmartFileSystem\Tests\Normalizer\PathNormalizerTest
 */
final class PathNormalizer
{
    /**
     * @see https://regex101.com/r/d4F5Fm/1
     * @var string
     */
    private const SCHEME_PATH_REGEX = '#^([a-z]+)\\:\\/\\/(.+)#';
    /**
     * @see https://regex101.com/r/no28vw/1
     * @var string
     */
    private const TWO_AND_MORE_SLASHES_REGEX = '#/{2,}#';
    /**
     * @var string
     */
    private const SCHEME_UNDEFINED = 'undefined';
    public function normalizePath(string $originalPath) : string
    {
        $directorySeparator = \DIRECTORY_SEPARATOR;
        $matches = \RectorPrefix20220531\Nette\Utils\Strings::match($originalPath, self::SCHEME_PATH_REGEX);
        if ($matches !== null) {
            [, $scheme, $path] = $matches;
        } else {
            $scheme = self::SCHEME_UNDEFINED;
            $path = $originalPath;
        }
        $normalizedPath = \str_replace('\\', '/', $path);
        $path = \RectorPrefix20220531\Nette\Utils\Strings::replace($normalizedPath, self::TWO_AND_MORE_SLASHES_REGEX, '/');
        $pathRoot = \strncmp($path, '/', \strlen('/')) === 0 ? $directorySeparator : '';
        $pathParts = \explode('/', \trim($path, '/'));
        $normalizedPathParts = $this->normalizePathParts($pathParts, $scheme);
        $pathStart = $scheme !== self::SCHEME_UNDEFINED ? $scheme . '://' : '';
        return $pathStart . $pathRoot . \implode($directorySeparator, $normalizedPathParts);
    }
    /**
     * @param string[] $pathParts
     * @return string[]
     */
    private function normalizePathParts(array $pathParts, string $scheme) : array
    {
        $normalizedPathParts = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }
            if ($pathPart !== '..') {
                $normalizedPathParts[] = $pathPart;
                continue;
            }
            /** @var string $removedPart */
            $removedPart = \array_pop($normalizedPathParts);
            if ($scheme !== 'phar') {
                continue;
            }
            if (\substr_compare($removedPart, '.phar', -\strlen('.phar')) !== 0) {
                continue;
            }
            $scheme = self::SCHEME_UNDEFINED;
        }
        return $normalizedPathParts;
    }
}
