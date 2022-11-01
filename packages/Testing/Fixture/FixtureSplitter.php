<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use RectorPrefix202211\Nette\Utils\FileSystem;
use RectorPrefix202211\Nette\Utils\Strings;
use RectorPrefix202211\Webmozart\Assert\Assert;
final class FixtureSplitter
{
    /**
     * @var string
     * @see https://regex101.com/r/zZDoyy/1
     */
    public const SPLIT_LINE_REGEX = '#\\-\\-\\-\\-\\-\\r?\\n#';
    /**
     * @return array<string, string>
     */
    public static function loadFileAndSplitInputAndExpected(string $filePath) : array
    {
        Assert::fileExists($filePath);
        $fixtureFileContents = FileSystem::read($filePath);
        return Strings::split($fixtureFileContents, self::SPLIT_LINE_REGEX);
    }
}
