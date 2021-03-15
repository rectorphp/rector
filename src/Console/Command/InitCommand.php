<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\FileSystemGuard;
use Symplify\SmartFileSystem\SmartFileSystem;

final class InitCommand extends Command
{
    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        FileSystemGuard $fileSystemGuard,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->smartFileSystem = $smartFileSystem;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate rector.php configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectorTemplateFilePath = __DIR__ . '/../../../templates/rector.php.dist';
        $this->fileSystemGuard->ensureFileExists($rectorTemplateFilePath, __METHOD__);

        $rectorRootFilePath = getcwd() . '/rector.php';

        $doesFileExist = $this->smartFileSystem->exists($rectorRootFilePath);
        if ($doesFileExist) {
            $this->symfonyStyle->warning('Config file "rector.php" already exists');
        } else {
            $this->smartFileSystem->copy($rectorTemplateFilePath, $rectorRootFilePath);
            $this->symfonyStyle->success('"rector.php" config file was added');
        }

        return ShellCode::SUCCESS;
    }
}
