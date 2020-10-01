<?php

declare(strict_types=1);

namespace Rector\Restoration\NameMatcher;

use Nette\Loaders\RobotLoader;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;

final class ExistingClassesProvider
{
    /**
     * @var ComposerJsonFactory
     */
    private $composerJsonFactory;

    /**
     * @var string[]
     */
    private $existingClasses = [];

    public function __construct(ComposerJsonFactory $composerJsonFactory)
    {
        $this->composerJsonFactory = $composerJsonFactory;
    }

    /**
     * @return string[]
     */
    public function provide(): array
    {
        if ($this->existingClasses === []) {
            $psr4Paths = $this->getPsr4PathFromComposerJson();

            $existingClasses = $this->findClassesInDirectories($psr4Paths);

            /** @var string[] $existingClasses */
            $existingClasses = array_merge($existingClasses, get_declared_classes());

            $this->existingClasses = $existingClasses;
        }

        return $this->existingClasses;
    }

    /**
     * @return string[]
     */
    private function getPsr4PathFromComposerJson(): array
    {
        $composerJsonFilePath = getcwd() . '/composer.json';
        $composerJson = $this->composerJsonFactory->createFromFilePath($composerJsonFilePath);

        $psr4Paths = $composerJson->getAutoload()['psr-4'] ?? [];
        $devPsr4Paths = $composerJson->getAutoloadDev()['psr-4'] ?? [];

        return array_merge($psr4Paths, $devPsr4Paths);
    }

    /**
     * @param string[] $psr4Paths
     * @return string[]
     */
    private function findClassesInDirectories(array $psr4Paths): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/rector_restore');

        // weird test files
        $robotLoader->ignoreDirs[] = '*Expected*';
        $robotLoader->ignoreDirs[] = '*Fixture*';

        foreach ($psr4Paths as $namespacePrefix => $path) {
            $robotLoader->addDirectory(getcwd() . '/' . $path);
        }

        return array_keys($robotLoader->getIndexedClasses());
    }
}
