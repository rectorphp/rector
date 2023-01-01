<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use RectorPrefix202301\Nette\Utils\FileSystem;
use RectorPrefix202301\Nette\Utils\Strings;
use RectorPrefix202301\Webmozart\Assert\Assert;
final class FixtureSplitter
{
    /**
     * @api
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
