<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Nette\Loaders\RobotLoader;
use Rector\Configuration\Option;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Console\Input\InputInterface;

final class AdditionalAutoloader
{
    /**
     * @var FileGuard
     */
    private $fileGuard;

    /**
     * @var string[]
     */
    private $autoloadFiles = [];

    /**
     * @var string[]
     */
    private $autoloadDirectories = [];

    /**
     * @param string[] $autoloadFiles
     * @param string[] $autoloadDirectories
     */
    public function __construct(array $autoloadFiles, array $autoloadDirectories)
    {
        $this->autoloadFiles = $autoloadFiles;
        $this->autoloadDirectories = $autoloadDirectories;
    }

    public function autoloadWithInput(InputInterface $input): void
    {
        $this->autoloadFileFromInput($input);

        if ($this->autoloadDirectories) {
            $this->autoloadDirectories($this->autoloadDirectories);
        }

        if ($this->autoloadFiles) {
            $this->autoloadFiles($this->autoloadFiles);
        }
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
