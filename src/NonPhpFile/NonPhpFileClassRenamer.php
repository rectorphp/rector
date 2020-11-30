<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Nette\Utils\Strings;

/**
 * @see \Rector\Core\Tests\NonPhpFile\NonPhpFileClassRenamer\NonPhpFileClassRenamerTest
 */
final class NonPhpFileClassRenamer
{
    /**
     * @see https://regex101.com/r/HKUFJD/7
     * for "?<!" @see https://stackoverflow.com/a/3735908/1348344
     * @var string
     */
    private const STANDALONE_CLASS_PREFIX_REGEX = '#((?<!(\\\\|"|\>|\.|\'))|(?<extra_space>\s+\\\\))';

    /**
     * @see https://regex101.com/r/HKUFJD/5
     * @see https://stackoverflow.com/a/3926546/1348344
     * @var string
     */
    private const STANDALONE_CLASS_SUFFIX_REGEX = '(?=::)#';

    /**
     * @param array<string, string> $classRenames
     */
    public function renameClasses(string $newContent, array $classRenames): string
    {
        $classRenames = $this->addDoubleSlahed($classRenames);

        foreach ($classRenames as $oldClass => $newClass) {
            // the old class is without slashes, it can make mess as similar to a word in the text, so we have to be more strict about it
            if (! Strings::contains($oldClass, '\\')) {
                $oldClassRegex = self::STANDALONE_CLASS_PREFIX_REGEX . preg_quote(
                    $oldClass,
                    '#'
                ) . self::STANDALONE_CLASS_SUFFIX_REGEX;
            } else {
                $oldClassRegex = '#' . preg_quote($oldClass, '#') . '#';
            }

            $newContent = Strings::replace($newContent, $oldClassRegex, function (array $match) use (
                $newClass
            ): string {
                return ($match['extra_space'] ?? '') . $newClass;
            });
        }

        return $newContent;
    }

    /**
     * Process with double quotes too, e.g. in twig
     *
     * @param array<string, string> $classRenames
     * @return array<string, string>
     */
    private function addDoubleSlahed(array $classRenames): array
    {
        foreach ($classRenames as $oldClass => $newClass) {
            // to prevent no slash override
            if (! Strings::contains($oldClass, '\\')) {
                continue;
            }

            $doubleSlashOldClass = str_replace('\\', '\\\\', $oldClass);
            $doubleSlashNewClass = str_replace('\\', '\\\\', $newClass);

            $classRenames[$doubleSlashOldClass] = $doubleSlashNewClass;
        }

        return $classRenames;
    }
}
