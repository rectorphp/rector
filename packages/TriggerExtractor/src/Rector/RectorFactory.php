<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Rector;

use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\NotImplementedException;
use Rector\TriggerExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\TriggerExtractor\Deprecation\ClassMethodDeprecation;
use Rector\TriggerExtractor\Deprecation\DeprecationCollector;

/**
 * Creates rectors with propper setup based on found deprecations.
 */
final class RectorFactory
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var ConfigurableChangeMethodNameRector
     */
    private $configurableChangeMethodNameRector;

    public function __construct(
        DeprecationCollector $deprecationCollector,
        ConfigurableChangeMethodNameRector $configurableChangeMethodNameRector
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->configurableChangeMethodNameRector = $configurableChangeMethodNameRector;
    }

    /**
     * @return RectorInterface[]
     */
    public function createRectors(): array
    {
        $rectors = [];

        foreach ($this->deprecationCollector->getDeprecations() as $deprecation) {
            $rectors[] = $this->createRectorFromDeprecation($deprecation);
        }

        return $rectors;
    }

    public function createRectorFromDeprecation(DeprecationInterface $deprecation): RectorInterface
    {
        if ($deprecation instanceof ClassMethodDeprecation) {
            $configurableChangeMethodNameRector = clone $this->configurableChangeMethodNameRector;
            $configurableChangeMethodNameRector->setPerClassOldToNewMethods([
                $deprecation->getClass() => [
                    $deprecation->getOldMethod() => $deprecation->getNewMethod()
                ]
            ]);

            return $configurableChangeMethodNameRector;
        }

        throw new NotImplementedException(sprintf(
            '%s::%s() was unable to create a Rector based on "%s" Deprecation. Create a new method there.',
            self::class,
            __METHOD__,
            get_class($deprecation)
        ));
    }
}
