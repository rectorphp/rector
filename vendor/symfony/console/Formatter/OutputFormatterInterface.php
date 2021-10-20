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
 * Formatter interface for console output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutputFormatterInterface
{
    /**
     * Sets the decorated flag.
     * @param bool $decorated
     */
    public function setDecorated($decorated);
    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated();
    /**
     * Sets a new style.
     * @param string $name
     * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style
     */
    public function setStyle($name, $style);
    /**
     * Checks if output formatter has style with specified name.
     *
     * @return bool
     * @param string $name
     */
    public function hasStyle($name);
    /**
     * Gets style options from style with specified name.
     *
     * @return OutputFormatterStyleInterface
     *
     * @throws \InvalidArgumentException When style isn't defined
     * @param string $name
     */
    public function getStyle($name);
    /**
     * Formats a message according to the given styles.
     * @param string|null $message
     */
    public function format($message);
}
