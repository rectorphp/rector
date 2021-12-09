<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211209\Symfony\Component\Console\Formatter;

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
     * Whether the output will decorate messages.
     */
    public function isDecorated() : bool;
    /**
     * Sets a new style.
     * @param string $name
     * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style
     */
    public function setStyle($name, $style);
    /**
     * Checks if output formatter has style with specified name.
     * @param string $name
     */
    public function hasStyle($name) : bool;
    /**
     * Gets style options from style with specified name.
     *
     * @throws \InvalidArgumentException When style isn't defined
     * @param string $name
     */
    public function getStyle($name) : \RectorPrefix20211209\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;
    /**
     * Formats a message according to the given styles.
     * @param string|null $message
     */
    public function format($message) : ?string;
}
