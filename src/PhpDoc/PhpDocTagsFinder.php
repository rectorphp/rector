<?php

declare(strict_types=1);

namespace Rector\Core\PhpDoc;

use Nette\Utils\Strings;

/**
 * @see \Rector\Core\Tests\PhpDoc\PhpDocTagsFinderTest
 */
final class PhpDocTagsFinder
{
    /**
     * Inspired by
     * https://github.com/nette/di/blob/d1c0598fdecef6d3b01e2ace5f2c30214b3108e6/src/DI/Autowiring.php#L215
     *
     * @var string
     * @see https://regex101.com/r/oEiq3y/3
     */
    private const TAG_REGEX = '#%s[ a-zA-Z0-9_\|\\\t]+#';

    /**
     * @return mixed[]
     */
    public function extractTagsFromStringedDocblock(string $dockblock, string $tagName): array
    {
        $tagName = '@' . ltrim($tagName, '@');
        $regEx = sprintf(self::TAG_REGEX, $tagName);
        $result = Strings::matchAll($dockblock, $regEx);
        if ($result === []) {
            return [];
        }

        $matchingTags = array_merge(...$result);

        $explode = static function (string $matchingTag) use ($tagName): array {
            // This is required as @return, for example, can be written as "@return ClassOne|ClassTwo|ClassThree"
            return explode('|', str_replace($tagName . ' ', '', $matchingTag));
        };

        $matchingTags = array_map($explode, $matchingTags);

        return array_merge(...$matchingTags);
    }
}
