<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Symfony\Component\Console\Output;

use RectorPrefix20211231\Symfony\Component\Console\Formatter\OutputFormatter;
use RectorPrefix20211231\Symfony\Component\Console\Formatter\OutputFormatterInterface;
/**
 * Base class for output classes.
 *
 * There are five levels of verbosity:
 *
 *  * normal: no option passed (normal output)
 *  * verbose: -v (more output)
 *  * very verbose: -vv (highly extended output)
 *  * debug: -vvv (all debug output)
 *  * quiet: -q (no output)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Output implements \RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface
{
    /**
     * @var int
     */
    private $verbosity;
    private $formatter;
    /**
     * @param int|null                      $verbosity The verbosity level (one of the VERBOSITY constants in OutputInterface)
     * @param bool                          $decorated Whether to decorate messages
     * @param OutputFormatterInterface|null $formatter Output formatter instance (null to use default OutputFormatter)
     */
    public function __construct(?int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = \false, \RectorPrefix20211231\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter = null)
    {
        $this->verbosity = $verbosity ?? self::VERBOSITY_NORMAL;
        $this->formatter = $formatter ?? new \RectorPrefix20211231\Symfony\Component\Console\Formatter\OutputFormatter();
        $this->formatter->setDecorated($decorated);
    }
    /**
     * {@inheritdoc}
     */
    public function setFormatter(\RectorPrefix20211231\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }
    /**
     * {@inheritdoc}
     */
    public function getFormatter() : \RectorPrefix20211231\Symfony\Component\Console\Formatter\OutputFormatterInterface
    {
        return $this->formatter;
    }
    /**
     * {@inheritdoc}
     */
    public function setDecorated(bool $decorated)
    {
        $this->formatter->setDecorated($decorated);
    }
    /**
     * {@inheritdoc}
     */
    public function isDecorated() : bool
    {
        return $this->formatter->isDecorated();
    }
    /**
     * {@inheritdoc}
     */
    public function setVerbosity(int $level)
    {
        $this->verbosity = $level;
    }
    /**
     * {@inheritdoc}
     */
    public function getVerbosity() : int
    {
        return $this->verbosity;
    }
    /**
     * {@inheritdoc}
     */
    public function isQuiet() : bool
    {
        return self::VERBOSITY_QUIET === $this->verbosity;
    }
    /**
     * {@inheritdoc}
     */
    public function isVerbose() : bool
    {
        return self::VERBOSITY_VERBOSE <= $this->verbosity;
    }
    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose() : bool
    {
        return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
    }
    /**
     * {@inheritdoc}
     */
    public function isDebug() : bool
    {
        return self::VERBOSITY_DEBUG <= $this->verbosity;
    }
    /**
     * {@inheritdoc}
     * @param mixed[]|string $messages
     */
    public function writeln($messages, int $options = self::OUTPUT_NORMAL)
    {
        $this->write($messages, \true, $options);
    }
    /**
     * {@inheritdoc}
     * @param mixed[]|string $messages
     */
    public function write($messages, bool $newline = \false, int $options = self::OUTPUT_NORMAL)
    {
        if (!\is_iterable($messages)) {
            $messages = [$messages];
        }
        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type = $types & $options ?: self::OUTPUT_NORMAL;
        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity = $verbosities & $options ?: self::VERBOSITY_NORMAL;
        if ($verbosity > $this->getVerbosity()) {
            return;
        }
        foreach ($messages as $message) {
            switch ($type) {
                case \RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface::OUTPUT_NORMAL:
                    $message = $this->formatter->format($message);
                    break;
                case \RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface::OUTPUT_RAW:
                    break;
                case \RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface::OUTPUT_PLAIN:
                    $message = \strip_tags($this->formatter->format($message));
                    break;
            }
            $this->doWrite($message ?? '', $newline);
        }
    }
    /**
     * Writes a message to the output.
     */
    protected abstract function doWrite(string $message, bool $newline);
}
