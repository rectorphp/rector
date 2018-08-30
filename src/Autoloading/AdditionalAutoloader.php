<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use Rector\Configuration\Option;
use Rector\FileSystem\FileGuard;
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
     * @param string[] $autoloadFiles
     * @param string[] $autoloadDirectories
     */
    public function __construct(array $autoloadFiles, array $autoloadDirectories, FileGuard $fileGuard)
    {
        $this->autoloadFiles = $autoloadFiles;
        $this->autoloadDirectories = $autoloadDirectories;
        $this->fileGuard = $fileGuard;
    }

    public function autoloadWithInput(InputInterface $input): void
    {
        $this->autoloadFileFromInput($input);
        $this->autoloadDirectories($this->autoloadDirectories);
        $this->autoloadFiles($this->autoloadFiles);
    }

    /**
     * @param string[] $source
     */
    public function autoloadWithInputAndSource(InputInterface $input, array $source): void
    {
        $this->autoloadWithInput($input);

        [$files, $directories] = $this->splitSourceToDirectoriesAndFiles($source);

        // @todo include the files so people don't have to do it manually?

        $absoluteDirectories = [];

        foreach ($directories as $directory) {
            if (Strings::contains($directory, '*')) { // is fnmatch for directories
                $absoluteDirectories = array_merge($absoluteDirectories, glob($directory, GLOB_ONLYDIR));
            } else { // is classic directory
                $absoluteDirectories[] = $directory;
            }
        }

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
     * @todo decouple to standalone Finder/File something
     * @param string[] $source
     * @return string[][]|string[]
     */
    private function splitSourceToDirectoriesAndFiles(array $source): array
    {
        $files = [];
        $directories = [];

        foreach ($source as $singleSource) {
            if (is_file($singleSource)) {
                $files[] = $singleSource;
            } else {
                $directories[] = $singleSource;
            }
        }

        return [$files, $directories];
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
