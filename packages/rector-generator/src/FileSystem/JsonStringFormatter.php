<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\FileSystem;

use Nette\Utils\Strings;

final class JsonStringFormatter
{
    /**
     * @var string
     * @see https://regex101.com/r/T0Aq6F/1
     */
    private const REPLACE_REGEX = '#(?<start>"authors": \[\s+)(?<content>.*?)(?<end>\s+\](,))#ms';

    /**
     * @param string[] $sections
     */
    public function inlineSections(string $jsonContent, array $sections): string
    {
        foreach ($sections as $section) {
            $pattern = '#("' . preg_quote($section, '#') . '": )\[(.*?)\](,)#ms';
            $jsonContent = Strings::replace($jsonContent, $pattern, function (array $match): string {
                $inlined = Strings::replace($match[2], '#\s+#', ' ');
                $inlined = trim($inlined);
                $inlined = '[' . $inlined . ']';
                return $match[1] . $inlined . $match[3];
            });
        }

        return $jsonContent;
    }

    public function inlineAuthors(string $jsonContent): string
    {
        return Strings::replace($jsonContent, self::REPLACE_REGEX, function (array $match): string {
            $inlined = Strings::replace($match['content'], '#\s+#', ' ');
            $inlined = trim($inlined);
            $inlined = Strings::replace($inlined, '#},#', "},\n       ");

            return $match['start'] . $inlined . $match['end'];
        });
    }
}
