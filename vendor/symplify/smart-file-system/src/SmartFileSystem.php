<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\SmartFileSystem;

use RectorPrefix20220501\Nette\Utils\Strings;
use RectorPrefix20220501\Symfony\Component\Filesystem\Exception\IOException;
use RectorPrefix20220501\Symfony\Component\Filesystem\Filesystem;
/**
 * @see \Symplify\SmartFileSystem\Tests\SmartFileSystem\SmartFileSystemTest
 */
final class SmartFileSystem extends \RectorPrefix20220501\Symfony\Component\Filesystem\Filesystem
{
    /**
     * @var string
     * @see https://regex101.com/r/tx6eyw/1
     */
    private const BEFORE_COLLON_REGEX = '#^\\w+\\(.*?\\): #';
    /**
     * @see https://github.com/symfony/filesystem/pull/4/files
     */
    public function readFile(string $filename) : string
    {
        $source = @\file_get_contents($filename);
        if (!$source) {
            $message = \sprintf('Failed to read "%s" file: "%s"', $filename, $this->getLastError());
            throw new \RectorPrefix20220501\Symfony\Component\Filesystem\Exception\IOException($message, 0, null, $filename);
        }
        return $source;
    }
    public function readFileToSmartFileInfo(string $filename) : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return new \Symplify\SmartFileSystem\SmartFileInfo($filename);
    }
    /**
     * Converts given HTML code to plain text
     *
     * @source https://github.com/nette/utils/blob/e7bd59f1dd860d25dbbb1ac720dddd0fa1388f4c/src/Utils/Html.php#L325-L331
     */
    public function htmlToText(string $html) : string
    {
        $content = \strip_tags($html);
        return \html_entity_decode($content, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
    }
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return string[]
     */
    public function resolveFilePathsFromFileInfos(array $fileInfos) : array
    {
        $filePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $filePaths[] = $fileInfo->getRelativeFilePathFromCwd();
        }
        return $filePaths;
    }
    /**
     * Returns the last PHP error as plain string.
     *
     * @source https://github.com/nette/utils/blob/ab8eea12b8aacc7ea5bdafa49b711c2988447994/src/Utils/Helpers.php#L31-L40
     */
    private function getLastError() : string
    {
        $message = \error_get_last()['message'] ?? '';
        $htmlMessage = \ini_get('html_errors') ? $this->htmlToText($message) : $message;
        return \RectorPrefix20220501\Nette\Utils\Strings::replace($htmlMessage, self::BEFORE_COLLON_REGEX, '');
    }
}
