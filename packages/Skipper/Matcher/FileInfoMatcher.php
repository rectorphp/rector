<?php

declare (strict_types=1);
namespace Rector\Skipper\Matcher;

use Rector\Skipper\FileSystem\FnMatchPathNormalizer;
use Rector\Skipper\Fnmatcher;
final class FileInfoMatcher
{
    /**
     * @readonly
     * @var \Rector\Skipper\FileSystem\FnMatchPathNormalizer
     */
    private $fnMatchPathNormalizer;
    /**
     * @readonly
     * @var \Rector\Skipper\Fnmatcher
     */
    private $fnmatcher;
    public function __construct(FnMatchPathNormalizer $fnMatchPathNormalizer, Fnmatcher $fnmatcher)
    {
        $this->fnMatchPathNormalizer = $fnMatchPathNormalizer;
        $this->fnmatcher = $fnmatcher;
    }
    /**
     * @param string[] $filePatterns
     */
    public function doesFileInfoMatchPatterns(string $filePath, array $filePatterns) : bool
    {
        foreach ($filePatterns as $filePattern) {
            if ($this->doesFileMatchPattern($filePath, $filePattern)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Supports both relative and absolute $file path. They differ for PHP-CS-Fixer and PHP_CodeSniffer.
     */
    private function doesFileMatchPattern(string $filePath, string $ignoredPath) : bool
    {
        // in ecs.php, the path can be absolute
        if ($filePath === $ignoredPath) {
            return \true;
        }
        $ignoredPath = $this->fnMatchPathNormalizer->normalizeForFnmatch($ignoredPath);
        if ($ignoredPath === '') {
            return \false;
        }
        if (\strncmp($filePath, $ignoredPath, \strlen($ignoredPath)) === 0) {
            return \true;
        }
        if (\substr_compare($filePath, $ignoredPath, -\strlen($ignoredPath)) === 0) {
            return \true;
        }
        return $this->fnmatcher->match($ignoredPath, $filePath);
    }
}
