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

use RectorPrefix202507\Symfony\Component\Console\Formatter\NullOutputFormatter;
use RectorPrefix202507\Symfony\Component\Console\Formatter\OutputFormatterInterface;
/**
 * NullOutput suppresses all output.
 *
 *     $output = new NullOutput();
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class NullOutput implements OutputInterface
{
    private NullOutputFormatter $formatter;
    /**
     * @return void
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // do nothing
    }
    public function getFormatter() : OutputFormatterInterface
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return $this->formatter ??= new NullOutputFormatter();
    }
    /**
     * @return void
     */
    public function setDecorated(bool $decorated)
    {
        // do nothing
    }
    public function isDecorated() : bool
    {
        return \false;
    }
    /**
     * @return void
     */
    public function setVerbosity(int $level)
    {
        // do nothing
    }
    public function getVerbosity() : int
    {
        return self::VERBOSITY_QUIET;
    }
    public function isQuiet() : bool
    {
        return \true;
    }
    public function isVerbose() : bool
    {
        return \false;
    }
    public function isVeryVerbose() : bool
    {
        return \false;
    }
    public function isDebug() : bool
    {
        return \false;
    }
    /**
     * @return void
     * @param string|iterable $messages
     */
    public function writeln($messages, int $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
    /**
     * @return void
     * @param string|iterable $messages
     */
    public function write($messages, bool $newline = \false, int $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
}
