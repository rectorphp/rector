<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Php\PhpVersionProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileSystem;

final class InitCommand extends Command
{
    /**
     * @var string
     */
    private const TEMPLATE_PATH = __DIR__ . '/../../../templates/rector.php.dist';

    public function __construct(
        private readonly SmartFileSystem $smartFileSystem,
        private readonly OutputStyleInterface $rectorOutputStyle,
        private readonly PhpVersionProvider $phpVersionProvider,
        private readonly SymfonyStyle $symfonyStyle
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('init');

        $this->setDescription('Generate rector.php configuration file');

        // deprecated
        $this->addOption(
            Option::TEMPLATE_TYPE,
            null,
            InputOption::VALUE_OPTIONAL,
            'A template type like default, nette, doctrine etc.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $templateType = (string) $input->getOption(Option::TEMPLATE_TYPE);
        if ($templateType !== '') {
            // notice warning
            $this->symfonyStyle->warning(
                'The option "--type" is deprecated. Custom config should be part of project documentation instead.'
            );
            sleep(3);
        }

        $rectorRootFilePath = getcwd() . '/rector.php';

        $doesFileExist = $this->smartFileSystem->exists($rectorRootFilePath);
        if ($doesFileExist) {
            $this->rectorOutputStyle->warning('Config file "rector.php" already exists');
        } else {
            $this->smartFileSystem->copy(self::TEMPLATE_PATH, $rectorRootFilePath);

            $fullPHPVersion = (string) $this->phpVersionProvider->provide();
            $phpVersion = Strings::substring($fullPHPVersion, 0, 1) . Strings::substring($fullPHPVersion, 2, 1);

            $fileContent = $this->smartFileSystem->readFile($rectorRootFilePath);
            $fileContent = str_replace(
                'LevelSetList::UP_TO_PHP_XY',
                'LevelSetList::UP_TO_PHP_' . $phpVersion,
                $fileContent
            );
            $this->smartFileSystem->dumpFile($rectorRootFilePath, $fileContent);

            $this->rectorOutputStyle->success('"rector.php" config file was added');
        }

        return Command::SUCCESS;
    }
}
