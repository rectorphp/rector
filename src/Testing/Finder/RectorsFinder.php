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
        $robotLoader = new RobotLoader();
        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_finder');
        $robotLoader->acceptFiles = ['*Rector.php'];
        $robotLoader->rebuild();

        $rectors = [];
        foreach (array_keys($robotLoader->getIndexedClasses()) as $class) {
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
                // lowercase letter bug in RototLoader
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

        usort(
            $rectors,
            function (RectorInterface $firstRector, RectorInterface $secondRector): int {
                $firstRectorShortClass = Strings::after(get_class($firstRector), '\\', -1);
                $secondRectorShortClass = Strings::after(get_class($secondRector), '\\', -1);

                return $firstRectorShortClass <=> $secondRectorShortClass;
            }
        );

        return $rectors;
    }
}
