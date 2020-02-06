<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Console\Shell;
use Rector\Core\FileSystem\ClassFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ScanClassesCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private const SOURCE_ARGUMENT = 'source';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ClassFinder
     */
    private $classFinder;

    public function __construct(SymfonyStyle $symfonyStyle, ClassFinder $classFinder)
    {
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
        $this->classFinder = $classFinder;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show classes in provided source');

        $this->addArgument(self::SOURCE_ARGUMENT, InputArgument::REQUIRED, 'Path to file to be scanned');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $source */
        $source = (array) $input->getArgument(self::SOURCE_ARGUMENT);

        $foundClasses = $this->classFinder->findClassesInDirectories($source);
        $this->symfonyStyle->listing($foundClasses);

        $this->symfonyStyle->success(sprintf('We found %d classes', count($foundClasses)));

        return Shell::CODE_SUCCESS;
    }
}
