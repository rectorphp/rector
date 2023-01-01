<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202301\Symfony\Component\String\Slugger;

use RectorPrefix202301\Symfony\Component\Intl\Transliterator\EmojiTransliterator;
use RectorPrefix202301\Symfony\Component\String\AbstractUnicodeString;
use RectorPrefix202301\Symfony\Component\String\UnicodeString;
use RectorPrefix202301\Symfony\Contracts\Translation\LocaleAwareInterface;
if (!\interface_exists(LocaleAwareInterface::class)) {
    throw new \LogicException('You cannot use the "Symfony\\Component\\String\\Slugger\\AsciiSlugger" as the "symfony/translation-contracts" package is not installed. Try running "composer require symfony/translation-contracts".');
}
/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class AsciiSlugger implements SluggerInterface, LocaleAwareInterface
{
    private const LOCALE_TO_TRANSLITERATOR_ID = ['am' => 'Amharic-Latin', 'ar' => 'Arabic-Latin', 'az' => 'Azerbaijani-Latin', 'be' => 'Belarusian-Latin', 'bg' => 'Bulgarian-Latin', 'bn' => 'Bengali-Latin', 'de' => 'de-ASCII', 'el' => 'Greek-Latin', 'fa' => 'Persian-Latin', 'he' => 'Hebrew-Latin', 'hy' => 'Armenian-Latin', 'ka' => 'Georgian-Latin', 'kk' => 'Kazakh-Latin', 'ky' => 'Kirghiz-Latin', 'ko' => 'Korean-Latin', 'mk' => 'Macedonian-Latin', 'mn' => 'Mongolian-Latin', 'or' => 'Oriya-Latin', 'ps' => 'Pashto-Latin', 'ru' => 'Russian-Latin', 'sr' => 'Serbian-Latin', 'sr_Cyrl' => 'Serbian-Latin', 'th' => 'Thai-Latin', 'tk' => 'Turkmen-Latin', 'uk' => 'Ukrainian-Latin', 'uz' => 'Uzbek-Latin', 'zh' => 'Han-Latin'];
    /**
     * @var string|null
     */
    private $defaultLocale;
    /**
     * @var \Closure|mixed[]
     */
    private $symbolsMap = ['en' => ['@' => 'at', '&' => 'and']];
    /**
     * @var bool|string
     */
    private $emoji = \false;
    /**
     * Cache of transliterators per locale.
     *
     * @var \Transliterator[]
     */
    private $transliterators = [];
    /**
     * @param mixed[]|\Closure $symbolsMap
     */
    public function __construct(string $defaultLocale = null, $symbolsMap = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->symbolsMap = $symbolsMap ?? $this->symbolsMap;
    }
    public function setLocale(string $locale)
    {
        $this->defaultLocale = $locale;
    }
    public function getLocale() : string
    {
        return $this->defaultLocale;
    }
    /**
     * @param bool|string $emoji true will use the same locale,
     *                           false will disable emoji,
     *                           and a string to use a specific locale
     * @return $this
     */
    public function withEmoji($emoji = \true)
    {
        if (\false !== $emoji && !\class_exists(EmojiTransliterator::class)) {
            throw new \LogicException(\sprintf('You cannot use the "%s()" method as the "symfony/intl" package is not installed. Try running "composer require symfony/intl".', __METHOD__));
        }
        $new = clone $this;
        $new->emoji = $emoji;
        return $new;
    }
    public function slug(string $string, string $separator = '-', string $locale = null) : AbstractUnicodeString
    {
        $locale = $locale ?? $this->defaultLocale;
        $transliterator = [];
        if ($locale && ('de' === $locale || \strncmp($locale, 'de_', \strlen('de_')) === 0)) {
            // Use the shortcut for German in UnicodeString::ascii() if possible (faster and no requirement on intl)
            $transliterator = ['de-ASCII'];
        } elseif (\function_exists('transliterator_transliterate') && $locale) {
            $transliterator = (array) $this->createTransliterator($locale);
        }
        if ($emojiTransliterator = $this->createEmojiTransliterator($locale)) {
            $transliterator[] = $emojiTransliterator;
        }
        if ($this->symbolsMap instanceof \Closure) {
            // If the symbols map is passed as a closure, there is no need to fallback to the parent locale
            // as the closure can just provide substitutions for all locales of interest.
            $symbolsMap = $this->symbolsMap;
            \array_unshift($transliterator, static function ($s) use($symbolsMap, $locale) {
                return $symbolsMap($s, $locale);
            });
        }
        $unicodeString = (new UnicodeString($string))->ascii($transliterator);
        if (\is_array($this->symbolsMap)) {
            $map = null;
            if (isset($this->symbolsMap[$locale])) {
                $map = $this->symbolsMap[$locale];
            } else {
                $parent = self::getParentLocale($locale);
                if ($parent && isset($this->symbolsMap[$parent])) {
                    $map = $this->symbolsMap[$parent];
                }
            }
            if ($map) {
                foreach ($map as $char => $replace) {
                    $unicodeString = $unicodeString->replace($char, ' ' . $replace . ' ');
                }
            }
        }
        return $unicodeString->replaceMatches('/[^A-Za-z0-9]++/', $separator)->trim($separator);
    }
    private function createTransliterator(string $locale) : ?\Transliterator
    {
        if (\array_key_exists($locale, $this->transliterators)) {
            return $this->transliterators[$locale];
        }
        // Exact locale supported, cache and return
        if ($id = self::LOCALE_TO_TRANSLITERATOR_ID[$locale] ?? null) {
            return $this->transliterators[$locale] = \Transliterator::create($id . '/BGN') ?? \Transliterator::create($id);
        }
        // Locale not supported and no parent, fallback to any-latin
        if (!($parent = self::getParentLocale($locale))) {
            return $this->transliterators[$locale] = null;
        }
        // Try to use the parent locale (ie. try "de" for "de_AT") and cache both locales
        if ($id = self::LOCALE_TO_TRANSLITERATOR_ID[$parent] ?? null) {
            $transliterator = \Transliterator::create($id . '/BGN') ?? \Transliterator::create($id);
        }
        return $this->transliterators[$locale] = $this->transliterators[$parent] = $transliterator ?? null;
    }
    private function createEmojiTransliterator(?string $locale) : ?EmojiTransliterator
    {
        if (\is_string($this->emoji)) {
            $locale = $this->emoji;
        } elseif (!$this->emoji) {
            return null;
        }
        while (null !== $locale) {
            try {
                return EmojiTransliterator::create("emoji-{$locale}");
            } catch (\IntlException $exception) {
                $locale = self::getParentLocale($locale);
            }
        }
        return null;
    }
    private static function getParentLocale(?string $locale) : ?string
    {
        if (!$locale) {
            return null;
        }
        if (\false === ($str = \strrchr($locale, '_'))) {
            // no parent locale
            return null;
        }
        return \substr($locale, 0, -\strlen($str));
    }
}
