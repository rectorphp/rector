<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Nette\Loaders\RobotLoader;
use Rector\Configuration\Option;
use Rector\FileSystem\FileGuard;
use Rector\Utils\FilesystemTweaker;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\FileSystem\FileSystem;

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
    private $autoloadPaths = [];

    /**
     * @var FilesystemTweaker
     */
    private $filesystemTweaker;

    /**
     * @var string[]
     */
    private $excludePaths = [];

    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @param string[] $autoloadPaths
     * @param string[] $excludePaths
     */
    public function __construct(
        FileGuard $fileGuard,
        FilesystemTweaker $filesystemTweaker,
        FileSystem $fileSystem,
        array $autoloadPaths,
        array $excludePaths
    ) {
        $this->fileGuard = $fileGuard;
        $this->filesystemTweaker = $filesystemTweaker;
        $this->fileSystem = $fileSystem;
        $this->autoloadPaths = $autoloadPaths;
        $this->excludePaths = $excludePaths;
    }

    /**
     * @param string[] $source
     */
    public function autoloadWithInputAndSource(InputInterface $input, array $source): void
    {
        [$autoloadFiles, $autoloadDirectories] = $this->fileSystem->separateFilesAndDirectories($this->autoloadPaths);

        $this->autoloadFileFromInput($input);
        $this->autoloadDirectories($autoloadDirectories);
        $this->autoloadFiles($autoloadFiles);

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
        foreach ($this->excludePaths as $excludePath) {
            $robotLoader->ignoreDirs[] = $excludePath;
        }
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
