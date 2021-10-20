<?php

declare (strict_types=1);
namespace Rector\Core\Validation\Collector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Rector\Naming\Naming\PropertyNaming;
use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
final class EmptyConfigurableRectorCollector
{
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(\RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->privatesAccessor = $privatesAccessor;
        $this->propertyNaming = $propertyNaming;
    }
    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    public function resolveEmptyConfigurable(array $rectors) : array
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
    private function collectEmptyConfigurableRectors(\Rector\Core\Contract\Rector\RectorInterface $rector, array $emptyConfigurableRectors) : array
    {
        $ruleDefinition = $rector->getRuleDefinition();
        /** @var ConfiguredCodeSample[] $codeSamples */
        $codeSamples = $ruleDefinition->getCodeSamples();
        foreach ($codeSamples as $codeSample) {
            $configuration = $codeSample->getConfiguration();
            if (!\is_array($configuration)) {
                continue;
            }
            foreach (\array_keys($configuration) as $key) {
                $key = $this->propertyNaming->underscoreToName($key);
                if (!\property_exists($rector, $key)) {
                    continue;
                }
                // @see https://github.com/rectorphp/rector-laravel/pull/19
                if (\strncmp($key, 'exclude', \strlen('exclude')) === 0) {
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
    private function shouldSkip(\Rector\Core\Contract\Rector\RectorInterface $rector) : bool
    {
        if (!$rector instanceof \Rector\Core\Contract\Rector\ConfigurableRectorInterface) {
            return \true;
        }
        // it seems always loaded
        return $rector instanceof \Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
    }
}
