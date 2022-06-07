<?php

declare (strict_types=1);
namespace Rector\Core\Console\Output;

use Rector\Core\Console\Style\RectorConsoleOutputStyle;
use Rector\Core\Contract\Console\OutputStyleInterface;
/**
 * This services helps to abstract from Symfony, and allow custom output formatters to use this Rector internal class.
 * It is very helpful while scoping Rector from analysed project.
 */
final class RectorOutputStyle implements OutputStyleInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Console\Style\RectorConsoleOutputStyle
     */
    private $rectorConsoleOutputStyle;
    public function __construct(RectorConsoleOutputStyle $rectorConsoleOutputStyle)
    {
        $this->rectorConsoleOutputStyle = $rectorConsoleOutputStyle;
    }
    public function progressStart(int $fileCount) : void
    {
        $this->rectorConsoleOutputStyle->createProgressBar($fileCount);
    }
    public function progressAdvance(int $step = 1) : void
    {
        $this->rectorConsoleOutputStyle->progressAdvance($step);
    }
    public function error(string $message) : void
    {
        $this->rectorConsoleOutputStyle->error($message);
    }
    public function warning(string $message) : void
    {
        $this->rectorConsoleOutputStyle->warning($message);
    }
    public function success(string $message) : void
    {
        $this->rectorConsoleOutputStyle->success($message);
    }
    public function note(string $message) : void
    {
        $this->rectorConsoleOutputStyle->note($message);
    }
    public function title(string $message) : void
    {
        $this->rectorConsoleOutputStyle->title($message);
    }
    public function writeln(string $message) : void
    {
        $this->rectorConsoleOutputStyle->writeln($message);
    }
    public function newLine(int $count = 1) : void
    {
        $this->rectorConsoleOutputStyle->newLine($count);
    }
    /**
     * @param string[] $elements
     */
    public function listing(array $elements) : void
    {
        $this->rectorConsoleOutputStyle->listing($elements);
    }
    public function isVerbose() : bool
    {
        return $this->rectorConsoleOutputStyle->isVerbose();
    }
    public function isDebug() : bool
    {
        return $this->rectorConsoleOutputStyle->isDebug();
    }
    public function setVerbosity(int $level) : void
    {
        $this->rectorConsoleOutputStyle->setVerbosity($level);
    }
}
