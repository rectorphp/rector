<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Command;

use Rector\Core\Configuration\Option;
use Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer\AnnotationReaderClassSyncer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class SyncAnnotationParserCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var AnnotationReaderClassSyncer
     */
    private $annotationReaderClassSyncer;

    public function __construct(
        AnnotationReaderClassSyncer $annotationReaderClassSyncer,
        SymfonyStyle $symfonyStyle,
        ParameterProvider $parameterProvider
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->parameterProvider = $parameterProvider;
        $this->annotationReaderClassSyncer = $annotationReaderClassSyncer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('[DEV] Generate value-preserving DocParser from doctrine/annotation');

        $this->addOption(
            Option::OPTION_DRY_RUN,
            'n',
            InputOption::VALUE_NONE,
            'See diff of changes, do not save them to files.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // disable imports
        $this->parameterProvider->changeParameter(Option::AUTO_IMPORT_NAMES, false);

        $dryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);

        $isSuccess = $this->annotationReaderClassSyncer->sync($dryRun);
        if (! $isSuccess) {
            $message = 'Doctrine Annotation files have changed, sync them: bin/rector sync-annotation-parser';
            $this->symfonyStyle->error($message);

            return ShellCode::ERROR;
        }

        $this->symfonyStyle->success('Constant preserving doctrine/annotation parser was updated');

        return ShellCode::SUCCESS;
    }
}
