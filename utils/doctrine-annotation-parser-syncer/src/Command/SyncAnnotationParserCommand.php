<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Command;

use Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer\AnnotationReaderClassSyncer;
use Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer\DocParserClassSyncer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class SyncAnnotationParserCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var DocParserClassSyncer
     */
    private $docParserClassSyncer;

    /**
     * @var AnnotationReaderClassSyncer
     */
    private $annotationReaderClassSyncer;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        DocParserClassSyncer $docParserClassSyncer,
        AnnotationReaderClassSyncer $annotationReaderClassSyncer
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->docParserClassSyncer = $docParserClassSyncer;
        $this->annotationReaderClassSyncer = $annotationReaderClassSyncer;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOC] Generate value-preserving DocParser from doctrine/annotation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->docParserClassSyncer->sync();
        $this->annotationReaderClassSyncer->sync();

        $this->symfonyStyle->success('Done');

        return ShellCode::SUCCESS;
    }
}
