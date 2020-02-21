<?php

declare(strict_types=1);

namespace Rector\PHPUnit\TestClassResolver;

use Nette\Loaders\RobotLoader;
use Rector\PHPUnit\Composer\ComposerAutoloadedDirectoryProvider;

final class PHPUnitTestCaseClassesProvider
{
    /**
     * @var string[]
     */
    private $phpUnitTestCaseClasses = [];

    /**
     * @var ComposerAutoloadedDirectoryProvider
     */
    private $composerAutoloadedDirectoryProvider;

    public function __construct(ComposerAutoloadedDirectoryProvider $composerAutoloadedDirectoryProvider)
    {
        $this->composerAutoloadedDirectoryProvider = $composerAutoloadedDirectoryProvider;
    }

    /**
     * @return string[]
     */
    public function provide(): array
    {
        if ($this->phpUnitTestCaseClasses !== []) {
            return $this->phpUnitTestCaseClasses;
        }

        $robotLoader = $this->createRobotLoadForDirectories();
        $robotLoader->rebuild();

        $this->phpUnitTestCaseClasses = array_keys($robotLoader->getIndexedClasses());

        return $this->phpUnitTestCaseClasses;
    }

    private function createRobotLoadForDirectories(): RobotLoader
    {
        $robotLoader = new RobotLoader();
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/tests_add_see_rector_tests');

        $directories = $this->composerAutoloadedDirectoryProvider->provide();
        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->acceptFiles = ['*Test.php'];
        $robotLoader->ignoreDirs[] = '*Expected*';
        $robotLoader->ignoreDirs[] = '*Fixture*';

        return $robotLoader;
    }
}
