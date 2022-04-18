<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Symplify\Skipper\Matcher;

use RectorPrefix20220418\Symplify\Skipper\FileSystem\FnMatchPathNormalizer;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FileInfoMatcher
{
    /**
     * @var \Symplify\Skipper\FileSystem\FnMatchPathNormalizer
     */
    private $fnMatchPathNormalizer;
    public function __construct(\RectorPrefix20220418\Symplify\Skipper\FileSystem\FnMatchPathNormalizer $fnMatchPathNormalizer)
    {
        $this->fnMatchPathNormalizer = $fnMatchPathNormalizer;
    }
    /**
     * @param string[] $filePatterns
     */
    public function doesFileInfoMatchPatterns(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $filePatterns) : bool
    {
        foreach ($filePatterns as $filePattern) {
            if ($this->doesFileInfoMatchPattern($smartFileInfo, $filePattern)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Supports both relative and absolute $file path. They differ for PHP-CS-Fixer and PHP_CodeSniffer.
     */
    private function doesFileInfoMatchPattern(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, string $ignoredPath) : bool
    {
        // in ecs.php, the path can be absolute
        if ($smartFileInfo->getRealPath() === $ignoredPath) {
            return \true;
        }
        $ignoredPath = $this->fnMatchPathNormalizer->normalizeForFnmatch($ignoredPath);
        if ($ignoredPath === '') {
            return \false;
        }
        if ($smartFileInfo->startsWith($ignoredPath)) {
            return \true;
        }
        if ($smartFileInfo->endsWith($ignoredPath)) {
            return \true;
        }
        return $smartFileInfo->doesFnmatch($ignoredPath);
    }
}
