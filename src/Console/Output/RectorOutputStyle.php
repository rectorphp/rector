<?php

declare (strict_types=1);
namespace Rector\Core\Console\Output;

use Rector\Core\Contract\Console\OutputStyleInterface;
use RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * This services helps to abstract from Symfony, and allow custom output formatters to use this Rector internal class.
 * It is very helpful while scoping Rector from analysed project.
 */
final class RectorOutputStyle implements \Rector\Core\Contract\Console\OutputStyleInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function error(string $message) : void
    {
        $this->symfonyStyle->error($message);
    }
    public function warning(string $message) : void
    {
        $this->symfonyStyle->warning($message);
    }
    public function success(string $message) : void
    {
        $this->symfonyStyle->success($message);
    }
    public function note(string $message) : void
    {
        $this->symfonyStyle->note($message);
    }
    public function title(string $message) : void
    {
        $this->symfonyStyle->title($message);
    }
    public function writeln(string $message) : void
    {
        $this->symfonyStyle->writeln($message);
    }
    public function newline(int $count = 1) : void
    {
        $this->symfonyStyle->newLine($count);
    }
    /**
     * @param string[] $elements
     */
    public function listing(array $elements) : void
    {
        $this->symfonyStyle->listing($elements);
    }
}
