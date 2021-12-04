<?php

declare(strict_types=1);

namespace Rector\Core\Validation\Collector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Rector\Naming\Naming\PropertyNaming;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;

final class EmptyConfigurableRectorCollector
{
    public function __construct(
        private readonly PrivatesAccessor $privatesAccessor,
        private readonly PropertyNaming $propertyNaming
    ) {
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    public function resolveEmptyConfigurable(array $rectors): array
    {
        $emptyConfigurableRectors = [];
        foreach ($rectors as $rector) {
            if ($this->shouldSkip($rector)) {
                continue;
            }

            $emptyConfigurableRectors = $this->collectEmptyConfigurableRectors($rector, $emptyConfigurableRectors);
        }

        return $emptyConfigurableRectors;
    }

    /**
     * @param RectorInterface[] $emptyConfigurableRectors
     * @return RectorInterface[]
     */
    private function collectEmptyConfigurableRectors(RectorInterface $rector, array $emptyConfigurableRectors): array
    {
        $ruleDefinition = $rector->getRuleDefinition();

        /** @var ConfiguredCodeSample[] $codeSamples */
        $codeSamples = $ruleDefinition->getCodeSamples();
        foreach ($codeSamples as $codeSample) {
            $configuration = $codeSample->getConfiguration();

            foreach (array_keys($configuration) as $key) {
                $key = $this->propertyNaming->underscoreToName($key);
                if (! property_exists($rector, $key)) {
                    continue;
                }

                // @see https://github.com/rectorphp/rector-laravel/pull/19
                if (str_starts_with($key, 'exclude')) {
                    continue;
                }

                $value = $this->privatesAccessor->getPrivateProperty($rector, $key);
                if ($value === []) {
                    $emptyConfigurableRectors[] = $rector;
                    return $emptyConfigurableRectors;
                }
            }
        }

        return $emptyConfigurableRectors;
    }

    private function shouldSkip(RectorInterface $rector): bool
    {
        if (! $rector instanceof ConfigurableRectorInterface) {
            return true;
        }

        // it seems always loaded
        return $rector instanceof RenameClassNonPhpRector;
    }
}
