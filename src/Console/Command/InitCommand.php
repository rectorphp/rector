<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileSystem;

final class InitCommand extends AbstractCommand
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * InitCommand constructor.
     */
    public function __construct(SmartFileSystem $smartFileSystem, SymfonyStyle $symfonyStyle)
    {
        parent::__construct();

        $this->smartFileSystem = $smartFileSystem;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Generate rector.php configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectorConfigFiles = $this->smartFileSystem->exists(getcwd() . '/rector.php');

        if (! $rectorConfigFiles) {
            $this->smartFileSystem->copy(__DIR__ . '/../../../templates/rector.php.dist', getcwd() . '/rector.php');
            $this->symfonyStyle->success('"rector.php" config file has been generated successfully!');
        } else {
            $this->symfonyStyle->error('Config file not generated. A "rector.php" configuration file already exists');
        }

        return ShellCode::SUCCESS;
    }
}
