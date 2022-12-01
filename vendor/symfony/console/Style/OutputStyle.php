<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202212\Symfony\Component\Console\Style;

use RectorPrefix202212\Symfony\Component\Console\Formatter\OutputFormatterInterface;
use RectorPrefix202212\Symfony\Component\Console\Helper\ProgressBar;
use RectorPrefix202212\Symfony\Component\Console\Output\ConsoleOutputInterface;
use RectorPrefix202212\Symfony\Component\Console\Output\OutputInterface;
/**
 * Decorates output to add console style guide helpers.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class OutputStyle implements OutputInterface, StyleInterface
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }
    public function newLine(int $count = 1)
    {
        $this->output->write(\str_repeat(\PHP_EOL, $count));
    }
    public function createProgressBar(int $max = 0) : ProgressBar
    {
        return new ProgressBar($this->output, $max);
    }
    /**
     * @param string|mixed[] $messages
     */
    public function write($messages, bool $newline = \false, int $type = self::OUTPUT_NORMAL)
    {
        $this->output->write($messages, $newline, $type);
    }
    /**
     * @param string|mixed[] $messages
     */
    public function writeln($messages, int $type = self::OUTPUT_NORMAL)
    {
        $this->output->writeln($messages, $type);
    }
    public function setVerbosity(int $level)
    {
        $this->output->setVerbosity($level);
    }
    public function getVerbosity() : int
    {
        return $this->output->getVerbosity();
    }
    public function setDecorated(bool $decorated)
    {
        $this->output->setDecorated($decorated);
    }
    public function isDecorated() : bool
    {
        return $this->output->isDecorated();
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->output->setFormatter($formatter);
    }
    public function getFormatter() : OutputFormatterInterface
    {
        return $this->output->getFormatter();
    }
    public function isQuiet() : bool
    {
        return $this->output->isQuiet();
    }
    public function isVerbose() : bool
    {
        return $this->output->isVerbose();
    }
    public function isVeryVerbose() : bool
    {
        return $this->output->isVeryVerbose();
    }
    public function isDebug() : bool
    {
        return $this->output->isDebug();
    }
    protected function getErrorOutput()
    {
        if (!$this->output instanceof ConsoleOutputInterface) {
            return $this->output;
        }
        return $this->output->getErrorOutput();
    }
}
