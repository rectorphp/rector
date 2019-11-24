<?php

declare(strict_types=1);

namespace Rector\Utils\RectorGenerator\FileSystem;

use Nette\Utils\Strings;

final class JsonStringFormatter
{
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
        $pattern = '#(?<start>"authors": \[\s+)(?<content>.*?)(?<end>\s+\](,))#ms';
        $jsonContent = Strings::replace($jsonContent, $pattern, function (array $match): string {
            $inlined = Strings::replace($match['content'], '#\s+#', ' ');
            $inlined = trim($inlined);
            $inlined = Strings::replace($inlined, '#},#', "},\n       ");

            return $match['start'] . $inlined . $match['end'];
        });

        return $jsonContent;
    }
}
