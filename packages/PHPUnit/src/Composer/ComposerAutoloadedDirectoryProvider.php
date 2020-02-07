<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Composer;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Core\Testing\PHPUnit\PHPUnitEnvironment;

final class ComposerAutoloadedDirectoryProvider
{
    /**
     * @var string[]
     */
    private const AUTOLOAD_SECTIONS = ['autolaod', 'autoload-dev'];

    /**
     * @var string
     */
    private $composerFilePath;

    public function __construct()
    {
        $this->composerFilePath = getcwd() . '/composer.json';
    }

    /**
     * @return string[]
     */
    public function provide(): array
    {
        if (PHPUnitEnvironment::isPHPUnitRun()) {
            return [getcwd() . '/src', getcwd() . '/tests', getcwd() . '/packages'];
        }

        $composerJson = $this->loadComposerJsonArray();
        $autoloadDirectories = [];

        foreach (self::AUTOLOAD_SECTIONS as $autoloadSection) {
            if (! isset($composerJson[$autoloadSection])) {
                continue;
            }

            $sectionDirectories = $this->collectDirectoriesFromAutoload($composerJson[$autoloadSection]);
            $autoloadDirectories = array_merge($autoloadDirectories, $sectionDirectories);
        }

        return $autoloadDirectories;
    }

    /**
     * @return mixed[]
     */
    private function loadComposerJsonArray(): array
    {
        if (! file_exists($this->composerFilePath)) {
            return [];
        }

        $composerFileContent = FileSystem::read($this->composerFilePath);

        return Json::decode($composerFileContent, Json::FORCE_ARRAY);
    }

    /**
     * @param string[] $composerJsonAutoload
     * @return string[]
     */
    private function collectDirectoriesFromAutoload(array $composerJsonAutoload): array
    {
        $autoloadDirectories = [];

        if (isset($composerJsonAutoload['psr-4'])) {
            /** @var string[] $psr4 */
            $psr4 = $composerJsonAutoload['psr-4'];
            $autoloadDirectories = array_merge($autoloadDirectories, $psr4);
        }

        if (isset($composerJsonAutoload['classmap'])) {
            /** @var string[] $classmap */
            $classmap = $composerJsonAutoload['classmap'];

            foreach ($classmap as $fileOrDirectory) {
                $fileOrDirectory = getcwd() . '/' . $fileOrDirectory;

                // skip file, we look only for directories
                if (file_exists($fileOrDirectory)) {
                    continue;
                }

                $autoloadDirectories[] = $fileOrDirectory;
            }
        }

        return array_values($autoloadDirectories);
    }
}
