<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202306\Symfony\Contracts\Translation;

use RectorPrefix202306\Symfony\Component\Translation\Exception\InvalidArgumentException;
/**
 * A trait to help implement TranslatorInterface and LocaleAwareInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
trait TranslatorTrait
{
    /**
     * @var string|null
     */
    private $locale;
    /**
     * @return void
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }
    public function getLocale() : string
    {
        return $this->locale ?: (\class_exists(\Locale::class) ? \Locale::getDefault() : 'en');
    }
    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null) : string
    {
        if (null === $id || '' === $id) {
            return '';
        }
        if (!isset($parameters['%count%']) || !\is_numeric($parameters['%count%'])) {
            return \strtr($id, $parameters);
        }
        $number = (float) $parameters['%count%'];
        $locale = $locale ?: $this->getLocale();
        $parts = [];
        if (\preg_match('/^\\|++$/', $id)) {
            $parts = \explode('|', $id);
        } elseif (\preg_match_all('/(?:\\|\\||[^\\|])++/', $id, $matches)) {
            $parts = $matches[0];
        }
        $intervalRegexp = <<<'EOF'
/^(?P<interval>
    ({\s*
        (\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)
    \s*})

        |

    (?P<left_delimiter>[\[\]])
        \s*
        (?P<left>-Inf|\-?\d+(\.\d+)?)
        \s*,\s*
        (?P<right>\+?Inf|\-?\d+(\.\d+)?)
        \s*
    (?P<right_delimiter>[\[\]])
)\s*(?P<message>.*?)$/xs
EOF;
        $standardRules = [];
        foreach ($parts as $part) {
            $part = \trim(\str_replace('||', '|', $part));
            // try to match an explicit rule, then fallback to the standard ones
            if (\preg_match($intervalRegexp, $part, $matches)) {
                if ($matches[2]) {
                    foreach (\explode(',', $matches[3]) as $n) {
                        if ($number == $n) {
                            return \strtr($matches['message'], $parameters);
                        }
                    }
                } else {
                    $leftNumber = '-Inf' === $matches['left'] ? -\INF : (float) $matches['left'];
                    $rightNumber = \is_numeric($matches['right']) ? (float) $matches['right'] : \INF;
                    if (('[' === $matches['left_delimiter'] ? $number >= $leftNumber : $number > $leftNumber) && (']' === $matches['right_delimiter'] ? $number <= $rightNumber : $number < $rightNumber)) {
                        return \strtr($matches['message'], $parameters);
                    }
                }
            } elseif (\preg_match('/^\\w+\\:\\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } else {
                $standardRules[] = $part;
            }
        }
        $position = $this->getPluralizationRule($number, $locale);
        if (!isset($standardRules[$position])) {
            // when there's exactly one rule given, and that rule is a standard
            // rule, use this rule
            if (1 === \count($parts) && isset($standardRules[0])) {
                return \strtr($standardRules[0], $parameters);
            }
            $message = \sprintf('Unable to choose a translation for "%s" with locale "%s" for value "%d". Double check that this translation has the correct plural options (e.g. "There is one apple|There are %%count%% apples").', $id, $locale, $number);
            if (\class_exists(InvalidArgumentException::class)) {
                throw new InvalidArgumentException($message);
            }
            throw new \InvalidArgumentException($message);
        }
        return \strtr($standardRules[$position], $parameters);
    }
    /**
     * Returns the plural position to use for the given locale and number.
     *
     * The plural rules are derived from code of the Zend Framework (2010-09-25),
     * which is subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    private function getPluralizationRule(float $number, string $locale) : int
    {
        $number = \abs($number);
        return ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'af' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'bn' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'bg' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ca' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'da' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'de' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'el' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'en' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'en_US_POSIX' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'eo' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'es' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'et' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'eu' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fa' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fi' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fo' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fur' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fy' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'gl' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'gu' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ha' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'he' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'hu' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'is' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'it' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ku' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'lb' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ml' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'mn' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'mr' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'nah' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'nb' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ne' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'nl' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'nn' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'no' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'oc' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'om' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'or' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'pa' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'pap' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ps' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'pt' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'so' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sq' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sv' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sw' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ta' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'te' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'tk' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ur' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'zu' ? 1 == $number ? 0 : 1 : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'am' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'bh' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fil' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'fr' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'gun' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'hi' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'hy' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ln' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'mg' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'nso' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'pt_BR' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ti' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'wa' ? $number < 2 ? 0 : 1 : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'be' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'bs' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'hr' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ru' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sh' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sr' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'uk' ? 1 == $number % 10 && 11 != $number % 100 ? 0 : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20) ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'cs' || ('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sk' ? 1 == $number ? 0 : ($number >= 2 && $number <= 4 ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ga' ? 1 == $number ? 0 : (2 == $number ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'lt' ? 1 == $number % 10 && 11 != $number % 100 ? 0 : ($number % 10 >= 2 && ($number % 100 < 10 || $number % 100 >= 20) ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'sl' ? 1 == $number % 100 ? 0 : (2 == $number % 100 ? 1 : (3 == $number % 100 || 4 == $number % 100 ? 2 : 3)) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'mk' ? 1 == $number % 10 ? 0 : 1 : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'mt' ? 1 == $number ? 0 : (0 == $number || $number % 100 > 1 && $number % 100 < 11 ? 1 : ($number % 100 > 10 && $number % 100 < 20 ? 2 : 3)) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'lv' ? 0 == $number ? 0 : (1 == $number % 10 && 11 != $number % 100 ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'pl' ? 1 == $number ? 0 : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 12 || $number % 100 > 14) ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'cy' ? 1 == $number ? 0 : (2 == $number ? 1 : (8 == $number || 11 == $number ? 2 : 3)) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ro' ? 1 == $number ? 0 : (0 == $number || $number % 100 > 0 && $number % 100 < 20 ? 1 : 2) : (('pt_BR' !== $locale && 'en_US_POSIX' !== $locale && \strlen($locale) > 3 ? \substr($locale, 0, \strrpos($locale, '_')) : $locale) === 'ar' ? 0 == $number ? 0 : (1 == $number ? 1 : (2 == $number ? 2 : ($number % 100 >= 3 && $number % 100 <= 10 ? 3 : ($number % 100 >= 11 && $number % 100 <= 99 ? 4 : 5)))) : 0)))))))))))));
    }
}
