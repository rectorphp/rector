<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector;

use RectorPrefix20211020\Doctrine\Inflector\Rules\English;
use RectorPrefix20211020\Doctrine\Inflector\Rules\French;
use RectorPrefix20211020\Doctrine\Inflector\Rules\NorwegianBokmal;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Portuguese;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Spanish;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Turkish;
use InvalidArgumentException;
use function sprintf;
final class InflectorFactory
{
    public static function create() : \RectorPrefix20211020\Doctrine\Inflector\LanguageInflectorFactory
    {
        return self::createForLanguage(\RectorPrefix20211020\Doctrine\Inflector\Language::ENGLISH);
    }
    public static function createForLanguage(string $language) : \RectorPrefix20211020\Doctrine\Inflector\LanguageInflectorFactory
    {
        switch ($language) {
            case \RectorPrefix20211020\Doctrine\Inflector\Language::ENGLISH:
                return new \RectorPrefix20211020\Doctrine\Inflector\Rules\English\InflectorFactory();
            case \RectorPrefix20211020\Doctrine\Inflector\Language::FRENCH:
                return new \RectorPrefix20211020\Doctrine\Inflector\Rules\French\InflectorFactory();
            case \RectorPrefix20211020\Doctrine\Inflector\Language::NORWEGIAN_BOKMAL:
                return new \RectorPrefix20211020\Doctrine\Inflector\Rules\NorwegianBokmal\InflectorFactory();
            case \RectorPrefix20211020\Doctrine\Inflector\Language::PORTUGUESE:
                return new \RectorPrefix20211020\Doctrine\Inflector\Rules\Portuguese\InflectorFactory();
            case \RectorPrefix20211020\Doctrine\Inflector\Language::SPANISH:
                return new \RectorPrefix20211020\Doctrine\Inflector\Rules\Spanish\InflectorFactory();
            case \RectorPrefix20211020\Doctrine\Inflector\Language::TURKISH:
                return new \RectorPrefix20211020\Doctrine\Inflector\Rules\Turkish\InflectorFactory();
            default:
                throw new \InvalidArgumentException(\sprintf('Language "%s" is not supported.', $language));
        }
    }
}
