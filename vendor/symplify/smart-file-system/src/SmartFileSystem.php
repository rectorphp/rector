<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\SmartFileSystem;

use RectorPrefix202208\Nette\Utils\Strings;
use RectorPrefix202208\Symfony\Component\Filesystem\Exception\IOException;
use RectorPrefix202208\Symfony\Component\Filesystem\Filesystem;
/**
 * @see \Symplify\SmartFileSystem\Tests\SmartFileSystem\SmartFileSystemTest
 */
final class SmartFileSystem extends Filesystem
{
    /**
     * @var string
     * @see https://regex101.com/r/tx6eyw/1
     */
    private const BEFORE_COLLON_REGEX = '#^\\w+\\(.*?\\): #';
    /**
     * @see https://github.com/symfony/filesystem/pull/4/files
     */
    public function readFile(string $fileName) : string
    {
        $source = @\file_get_contents($fileName);
        if (!$source) {
            $message = \sprintf('Failed to read "%s" file: "%s"', $fileName, $this->getLastError());
            throw new IOException($message, 0, null, $fileName);
        }
        return $source;
    }
    public function readFileToSmartFileInfo(string $fileName) : SmartFileInfo
    {
        return new SmartFileInfo($fileName);
    }
    /**
     * Converts given HTML code to plain text
     *
     * @source https://github.com/nette/utils/blob/e7bd59f1dd860d25dbbb1ac720dddd0fa1388f4c/src/Utils/Html.php#L325-L331
     */
    private function htmlToText(string $html) : string
    {
        $content = \strip_tags($html);
        return \html_entity_decode($content, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
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
        return Strings::replace($htmlMessage, self::BEFORE_COLLON_REGEX, '');
    }
}
