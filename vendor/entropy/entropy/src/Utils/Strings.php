<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Utils;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Tests\Utils\StringsTest;
/**
 * @api to be used outside
 */
final class Strings
{
    public static function webalize(string $text): string
    {
        $text = (string) preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
        $text = trim($text, '-');
        return strtolower($text);
    }
}
