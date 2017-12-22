<?php declare(strict_types=1);

namespace Rector\DependencyInjection\Extension;

use Rector\Configuration\ConfigMerger;
use Rector\Configuration\Normalizer\RectorClassNormalizer;
use Rector\Configuration\Validator\RectorClassValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class RectorsExtension extends Extension
{
    /**
     * @var RectorClassValidator
     */
    private $rectorClassValidator;

    /**
     * @var RectorClassNormalizer
     */
    private $rectorClassNormalizer;

    /**
     * @var ConfigMerger
     */
    private $configMerger;

    public function __construct(
        RectorClassValidator $rectorClassValidator,
        RectorClassNormalizer $rectorClassNormalizer,
        ConfigMerger $configurationMerger
    ) {
        $this->rectorClassValidator = $rectorClassValidator;
        $this->rectorClassNormalizer = $rectorClassNormalizer;
        $this->configMerger = $configurationMerger;
    }

    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        if (! isset($configs[0])) {
            return;
        }

        $rectors = $this->configMerger->mergeConfigs($configs);
        $rectors = $this->rectorClassNormalizer->normalize($rectors);

        $this->rectorClassValidator->validate(array_keys($rectors));

        foreach ($rectors as $rectorClass => $arguments) {
            $rectorDefinition = $containerBuilder->autowire($rectorClass);
            if (! $arguments) {
                continue;
            }

            $rectorDefinition->setArguments([$arguments]);
        }
    }
}
