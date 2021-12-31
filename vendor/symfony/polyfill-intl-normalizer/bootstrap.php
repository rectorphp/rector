<?php



/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use RectorPrefix20211231\Symfony\Polyfill\Intl\Normalizer as p;
if (\PHP_VERSION_ID >= 80000) {
    return require __DIR__ . '/bootstrap80.php';
}
if (!\function_exists('normalizer_is_normalized')) {
    function normalizer_is_normalized($string, $form = \RectorPrefix20211231\Symfony\Polyfill\Intl\Normalizer\Normalizer::FORM_C)
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Intl\Normalizer\Normalizer::isNormalized($string, $form);
    }
}
if (!\function_exists('normalizer_normalize')) {
    function normalizer_normalize($string, $form = \RectorPrefix20211231\Symfony\Polyfill\Intl\Normalizer\Normalizer::FORM_C)
    {
        return \RectorPrefix20211231\Symfony\Polyfill\Intl\Normalizer\Normalizer::normalize($string, $form);
    }
}
