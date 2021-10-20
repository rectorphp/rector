<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\String\Slugger;

use RectorPrefix20211020\Symfony\Component\String\AbstractUnicodeString;
/**
 * Creates a URL-friendly slug from a given string.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface SluggerInterface
{
    /**
     * Creates a slug for the given string and locale, using appropriate transliteration when needed.
     * @param string $string
     * @param string $separator
     * @param string|null $locale
     */
    public function slug($string, $separator = '-', $locale = null) : \RectorPrefix20211020\Symfony\Component\String\AbstractUnicodeString;
}
