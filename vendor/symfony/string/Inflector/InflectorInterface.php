<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\String\Inflector;

interface InflectorInterface
{
    /**
     * Returns the singular forms of a string.
     *
     * If the method can't determine the form with certainty, several possible singulars are returned.
     *
     * @return string[] An array of possible singular forms
     * @param string $plural
     */
    public function singularize($plural) : array;
    /**
     * Returns the plural forms of a string.
     *
     * If the method can't determine the form with certainty, several possible plurals are returned.
     *
     * @return string[] An array of possible plural forms
     * @param string $singular
     */
    public function pluralize($singular) : array;
}
