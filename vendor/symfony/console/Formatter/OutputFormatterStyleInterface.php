<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Formatter;

/**
 * Formatter style interface for defining styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutputFormatterStyleInterface
{
    /**
     * Sets style foreground color.
     * @param string|null $color
     */
    public function setForeground($color = null);
    /**
     * Sets style background color.
     * @param string|null $color
     */
    public function setBackground($color = null);
    /**
     * Sets some specific style option.
     * @param string $option
     */
    public function setOption($option);
    /**
     * Unsets some specific style option.
     * @param string $option
     */
    public function unsetOption($option);
    /**
     * Sets multiple style options at once.
     * @param mixed[] $options
     */
    public function setOptions($options);
    /**
     * Applies the style to a given text.
     *
     * @return string
     * @param string $text
     */
    public function apply($text);
}
