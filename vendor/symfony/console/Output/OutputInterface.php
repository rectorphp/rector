<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202507\Symfony\Component\Console\Output;

use RectorPrefix202507\Symfony\Component\Console\Formatter\OutputFormatterInterface;
/**
 * OutputInterface is the interface implemented by all Output classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface OutputInterface
{
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;
    public const VERBOSITY_VERY_VERBOSE = 128;
    public const VERBOSITY_DEBUG = 256;
    public const OUTPUT_NORMAL = 1;
    public const OUTPUT_RAW = 2;
    public const OUTPUT_PLAIN = 4;
    /**
     * Writes a message to the output.
     *
     * @param bool $newline Whether to add a newline
     * @param int  $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                      0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return void
     * @param string|iterable $messages
     */
    public function write($messages, bool $newline = \false, int $options = 0);
    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                     0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return void
     * @param string|iterable $messages
     */
    public function writeln($messages, int $options = 0);
    /**
     * Sets the verbosity of the output.
     *
     * @param self::VERBOSITY_* $level
     *
     * @return void
     */
    public function setVerbosity(int $level);
    /**
     * Gets the current verbosity of the output.
     *
     * @return self::VERBOSITY_*
     */
    public function getVerbosity() : int;
    /**
     * Returns whether verbosity is quiet (-q).
     */
    public function isQuiet() : bool;
    /**
     * Returns whether verbosity is verbose (-v).
     */
    public function isVerbose() : bool;
    /**
     * Returns whether verbosity is very verbose (-vv).
     */
    public function isVeryVerbose() : bool;
    /**
     * Returns whether verbosity is debug (-vvv).
     */
    public function isDebug() : bool;
    /**
     * Sets the decorated flag.
     *
     * @return void
     */
    public function setDecorated(bool $decorated);
    /**
     * Gets the decorated flag.
     */
    public function isDecorated() : bool;
    /**
     * @return void
     */
    public function setFormatter(OutputFormatterInterface $formatter);
    /**
     * Returns current output formatter instance.
     */
    public function getFormatter() : OutputFormatterInterface;
}
