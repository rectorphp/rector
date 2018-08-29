<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Nette\Loaders\RobotLoader;
use Rector\Configuration\Option;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class AdditionalAutoloader
{
    /**
     * @var string
     */
    private const AUTOLOAD_DIRECTORIES_PARAMETER = 'autoload_directories';

    /**
     * @var string
     */
    private const AUTOLOAD_FILES_PARAMETER = 'autoload_files';

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var FileGuard
     */
    private $fileGuard;

    public function __construct(ParameterProvider $parameterProvider, FileGuard $fileGuard)
    {
        $this->parameterProvider = $parameterProvider;
        $this->fileGuard = $fileGuard;
    }

    public function autoloadWithInput(InputInterface $input): void
    {
        $this->autoloadFileFromInput($input);
        $this->autoloadDirectoriesFromParameter($this->parameterProvider);
    }

    private function autoloadFileFromInput(InputInterface $input): void
    {
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(Option::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }

        $this->autoloadFiles([$autoloadFile]);
    }

    private function autoloadDirectoriesFromParameter(ParameterProvider $parameterProvider): void
    {
        $autoloadDirectories = $parameterProvider->provideParameter(self::AUTOLOAD_DIRECTORIES_PARAMETER);
        if ($autoloadDirectories !== null) {
            $this->autoloadDirectories($autoloadDirectories);
        }

        $autoloadFiles = $parameterProvider->provideParameter(self::AUTOLOAD_FILES_PARAMETER);
        if ($autoloadFiles !== null) {
            $this->autoloadFiles($autoloadFiles);
        }
    }

    /**
     * @param string[] $directories
     */
    private function autoloadDirectories(array $directories): void
    {
        $robotLoader = new RobotLoader();
        $robotLoader->ignoreDirs[] = '*Fixtures';
        // last argument is workaround: https://github.com/nette/robot-loader/issues/12
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_robot_loader');

        foreach ($directories as $autoloadDirectory) {
            $robotLoader->addDirectory($autoloadDirectory);
        }

        $robotLoader->register();
    }

    /**
     * @param string[] $files
     */
    private function autoloadFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->fileGuard->ensureFileExists($file, 'Extra autoload');

            require_once $file;
        }
    }
}
