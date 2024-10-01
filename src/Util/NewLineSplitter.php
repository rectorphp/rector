<?php

declare (strict_types=1);
namespace Rector\Util;

use RectorPrefix202410\Nette\Utils\Strings;
final class NewLineSplitter
{
    /**
     * @var string
     * @see https://regex101.com/r/qduj2O/4
     */
    private const NEWLINES_REGEX = "#\r?\n#";
    /**
     * @return string[]
     */
    public static function split(string $content) : array
    {
        return Strings::split($content, self::NEWLINES_REGEX);
    }
}
