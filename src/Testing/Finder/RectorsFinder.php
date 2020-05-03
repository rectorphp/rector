<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Finder;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\Exception\ShouldNotHappenException;
use ReflectionClass;

final class RectorsFinder
{
    /**
     * @var string[]
     */
    private const RECTOR_PATHS = [
        __DIR__ . '/../../../rules',
        __DIR__ . '/../../../packages',
        __DIR__ . '/../../../src',
    ];

    /**
     * @return string[]
     */
    public function findCoreRectorClasses(): array
    {
        $allRectors = $this->findInDirectories(self::RECTOR_PATHS);

        return array_map(function (RectorInterface $rector): string {
            return get_class($rector);
        }, $allRectors);
    }

    /**
     * @return RectorInterface[]
     */
    public function findInDirectory(string $directory): array
    {
        return $this->findInDirectories([$directory]);
    }

    /**
     * @param string[] $directories
     * @return RectorInterface[]
     */
    public function findInDirectories(array $directories): array
    {
        $foundClasses = $this->findClassesInDirectoriesByName($directories, '*Rector.php');

        $rectors = [];
        foreach ($foundClasses as $class) {
            // special case, because robot loader is case insensitive
            if ($class === ExceptionCorrector::class) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->isAbstract()) {
                continue;
            }

            $rector = $reflectionClass->newInstanceWithoutConstructor();
            if (! $rector instanceof RectorInterface) {
                // lowercase letter bug in RobotLoader
                if (Strings::endsWith($class, 'rector')) {
                    continue;
                }

                throw new ShouldNotHappenException(sprintf(
                    '"%s" found something that looks like Rector but does not implements "%s" interface.',
                    __METHOD__,
                    RectorInterface::class
                ));
            }

            $rectors[] = $rector;
        }

        return $this->sortObjectsByShortClassName($rectors);
    }

    /**
     * @param string[] $directories
     * @return string[]
     */
    private function findClassesInDirectoriesByName(array $directories, string $name): array
    {
        $robotLoader = new RobotLoader();
        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_finder');
        $robotLoader->acceptFiles = [$name];
        $robotLoader->rebuild();

        return array_keys($robotLoader->getIndexedClasses());
    }

    /**
     * @param object[] $objects
     * @return object[]
     */
    private function sortObjectsByShortClassName(array $objects): array
    {
        usort(
            $objects,
            function (object $firstObject, object $secondObject): int {
                $firstRectorShortClass = Strings::after(get_class($firstObject), '\\', -1);
                $secondRectorShortClass = Strings::after(get_class($secondObject), '\\', -1);

                return $firstRectorShortClass <=> $secondRectorShortClass;
            }
        );

        return $objects;
    }
}
