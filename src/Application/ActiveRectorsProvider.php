<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\RectorGenerator\Contract\InternalRectorInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

/**
 * Provides list of Rector rules, that are not internal → only those registered by user
 */
final class ActiveRectorsProvider
{
    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, ParameterProvider $parameterProvider)
    {
        $excludeRectors = $parameterProvider->provideArrayParameter(Option::EXCLUDE_RECTORS);
        $this->ensureClassesExistsAndAreRectors($excludeRectors);

        foreach ($rectors as $key => $rector) {
            $rectorClass = get_class($rector);
            if (in_array($rectorClass, $excludeRectors, true)) {
                unset($rectors[$key]);
            }
        }

        $this->rectors = $rectors;
    }

    /**
     * @return RectorInterface[]
     */
    public function provideByType(string $type): array
    {
        return array_filter($this->rectors, function (RectorInterface $rector) use ($type): bool {
            return is_a($rector, $type, true);
        });
    }

    /**
     * @return RectorInterface[]
     */
    public function provide(): array
    {
        return $this->filterOutInternalRectorsAndSort($this->rectors);
    }

    /**
     * @param string[] $excludedRectors
     */
    private function ensureClassesExistsAndAreRectors(array $excludedRectors): void
    {
        foreach ($excludedRectors as $excludedRector) {
            $this->ensureClassExists($excludedRector);
            $this->ensureIsRectorClass($excludedRector);
        }
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function filterOutInternalRectorsAndSort(array $rectors): array
    {
        sort($rectors);

        return array_filter($rectors, function (RectorInterface $rector): bool {
            // utils rules
            if ($rector instanceof InternalRectorInterface) {
                return false;
            }

            // skip as internal and always run
            return ! $rector instanceof PostRectorInterface;
        });
    }

    private function ensureClassExists(string $excludedRector): void
    {
        if (class_exists($excludedRector)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" defined in "$parameters->set(Option::%s, [...])" was not found ',
            $excludedRector,
            'EXCLUDE_RECTORS'
        ));
    }

    private function ensureIsRectorClass(string $excludedRector): void
    {
        if (is_a($excludedRector, RectorInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" defined in "$parameters->set(Option::%s, [...]))" is not a Rector rule = does not implement "%s" ',
            $excludedRector,
            'EXCLUDE_RECTORS',
            RectorInterface::class
        ));
    }
}
