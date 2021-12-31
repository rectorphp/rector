<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Yaml;

use RectorPrefix20211231\Nette\Utils\Strings;
final class YamlIndentResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/kXv88L/1
     */
    private const FIRST_INDENT_REGEX = '#^(?<' . self::FIRST_INDENT_KEY . '>\\s+)[\\w\\-]#m';
    /**
     * @var string
     */
    private const FIRST_INDENT_KEY = 'first_indent';
    /**
     * @var int
     */
    private const DEFAULT_INDENT_SPACE_COUNT = 4;
    public function resolveIndentSpaceCount(string $yamlFileContent) : int
    {
        $firstSpaceMatch = \RectorPrefix20211231\Nette\Utils\Strings::match($yamlFileContent, self::FIRST_INDENT_REGEX);
        if (!isset($firstSpaceMatch[self::FIRST_INDENT_KEY])) {
            return self::DEFAULT_INDENT_SPACE_COUNT;
        }
        $firstIndent = $firstSpaceMatch[self::FIRST_INDENT_KEY];
        return \substr_count($firstIndent, ' ');
    }
}
