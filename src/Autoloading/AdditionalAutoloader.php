<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Nette\Loaders\RobotLoader;
use Rector\Console\Command\ProcessCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class AdditionalAutoloader
{
    /**
     * @var string
     */
    private const AUTOLOAD_DIRECTORIES_PARAMETER = 'autoload_directories';

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function autoloadWithInput(InputInterface $input): void
    {
        $this->autoloadFileFromInput($input);
        $this->autoloadDirectoriesFromParameter($this->parameterProvider);
    }

    private function autoloadFileFromInput(InputInterface $input): void
    {
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(ProcessCommand::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }

        if (! is_file($autoloadFile) || ! file_exists($autoloadFile)) {
            return;
        }

        require_once $autoloadFile;
    }

    private function autoloadDirectoriesFromParameter(ParameterProvider $parameterProvider): void
    {
        $autoloadDirectories = $parameterProvider->provideParameter(self::AUTOLOAD_DIRECTORIES_PARAMETER);
        if ($autoloadDirectories === null) {
            return;
        }

        $robotLoader = new RobotLoader();
        $robotLoader->ignoreDirs = ['*Fixtures'] + $robotLoader->ignoreDirs;
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_robot_loader');

        foreach ($autoloadDirectories as $autoloadDirectory) {
            $robotLoader->addDirectory($autoloadDirectory);
        }

        $robotLoader->register();
    }
}
