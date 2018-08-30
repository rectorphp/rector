<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Nette\Loaders\RobotLoader;
use Rector\Configuration\Option;
use Rector\FileSystem\FileGuard;
use Rector\Utils\FilesystemTweaker;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Should it pass autoload files/directories to PHPStan analyzer?
 */
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
     * @var FilesystemTweaker
     */
    private $filesystemTweaker;

    /**
     * @param string[] $autoloadFiles
     * @param string[] $autoloadDirectories
     */
    public function __construct(
        array $autoloadFiles,
        array $autoloadDirectories,
        FileGuard $fileGuard,
        FilesystemTweaker $filesystemTweaker
    ) {
        $this->autoloadFiles = $autoloadFiles;
        $this->autoloadDirectories = $autoloadDirectories;
        $this->fileGuard = $fileGuard;
        $this->filesystemTweaker = $filesystemTweaker;
    }

    /**
     * @param string[] $source
     */
    public function autoloadWithInputAndSource(InputInterface $input, array $source): void
    {
        $this->autoloadFileFromInput($input);
        $this->autoloadDirectories($this->autoloadDirectories);
        $this->autoloadFiles($this->autoloadFiles);

        [$files, $directories] = $this->filesystemTweaker->splitSourceToDirectoriesAndFiles($source);
        $this->autoloadFiles($files);

        $absoluteDirectories = $this->filesystemTweaker->resolveDirectoriesWithFnmatch($directories);
        if (count($absoluteDirectories)) {
            $this->autoloadDirectories($absoluteDirectories);
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
        if (! count($directories)) {
            return;
        }

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
