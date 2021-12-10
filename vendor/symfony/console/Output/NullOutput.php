<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211210\Symfony\Component\Console\Output;

use RectorPrefix20211210\Symfony\Component\Console\Formatter\NullOutputFormatter;
use RectorPrefix20211210\Symfony\Component\Console\Formatter\OutputFormatterInterface;
/**
 * NullOutput suppresses all output.
 *
 *     $output = new NullOutput();
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class NullOutput implements \RectorPrefix20211210\Symfony\Component\Console\Output\OutputInterface
{
    private $formatter;
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter
     */
    public function setFormatter($formatter)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function getFormatter() : \RectorPrefix20211210\Symfony\Component\Console\Formatter\OutputFormatterInterface
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return $this->formatter = $this->formatter ?? new \RectorPrefix20211210\Symfony\Component\Console\Formatter\NullOutputFormatter();
    }
    /**
     * {@inheritdoc}
     * @param bool $decorated
     */
    public function setDecorated($decorated)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function isDecorated() : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     * @param int $level
     */
    public function setVerbosity($level)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     */
    public function getVerbosity() : int
    {
        return self::VERBOSITY_QUIET;
    }
    /**
     * {@inheritdoc}
     */
    public function isQuiet() : bool
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function isVerbose() : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose() : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function isDebug() : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     * @param mixed[]|string $messages
     * @param int $options
     */
    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param mixed[]|string $messages
     * @param bool $newline
     * @param int $options
     */
    public function write($messages, $newline = \false, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
}
