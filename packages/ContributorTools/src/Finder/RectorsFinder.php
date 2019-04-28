<?php declare(strict_types=1);

namespace Rector\ContributorTools\Finder;

use Nette\Loaders\RobotLoader;
use Rector\Contract\Rector\RectorInterface;
use Rector\Error\ExceptionCorrector;
use Rector\Exception\ShouldNotHappenException;
use ReflectionClass;

final class RectorsFinder
{
    /**
     * @return string[]
     */
    public function findCoreRectorClasses(): array
    {
        $allRectors = $this->findInDirectories([__DIR__ . '/../../../../packages', __DIR__ . '/../../../../src']);

        return array_map(function (RectorInterface $rector) {
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
                throw new ShouldNotHappenException(sprintf(
                    '"%s" found something that looks like Rector but does not implements "%s" interface.',
                    __METHOD__,
                    RectorInterface::class
                ));
            }

            $rectors[] = $rector;
        }

        return $rectors;
    }
}
