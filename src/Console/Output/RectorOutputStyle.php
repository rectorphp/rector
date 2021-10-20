<?php

declare (strict_types=1);
namespace Rector\Core\Console\Output;

use Rector\Core\Contract\Console\OutputStyleInterface;
use RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * This services helps to abstract from Symfony, and allow custom output formatters to use this Rector internal class.
 * It is very helpful while scoping Rector from analysed project.
 */
final class RectorOutputStyle implements \Rector\Core\Contract\Console\OutputStyleInterface
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @param string $message
     */
    public function error($message) : void
    {
        $this->symfonyStyle->error($message);
    }
    /**
     * @param string $message
     */
    public function warning($message) : void
    {
        $this->symfonyStyle->warning($message);
    }
    /**
     * @param string $message
     */
    public function success($message) : void
    {
        $this->symfonyStyle->success($message);
    }
    /**
     * @param string $message
     */
    public function note($message) : void
    {
        $this->symfonyStyle->note($message);
    }
    /**
     * @param string $message
     */
    public function title($message) : void
    {
        $this->symfonyStyle->title($message);
    }
    /**
     * @param string $message
     */
    public function writeln($message) : void
    {
        $this->symfonyStyle->writeln($message);
    }
    /**
     * @param int $count
     */
    public function newline($count = 1) : void
    {
        $this->symfonyStyle->newLine($count);
    }
    /**
     * @param string[] $elements
     */
    public function listing($elements) : void
    {
        $this->symfonyStyle->listing($elements);
    }
}
