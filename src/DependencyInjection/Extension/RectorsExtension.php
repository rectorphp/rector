<?php declare(strict_types=1);

namespace Rector\DependencyInjection\Extension;

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

    public function __construct(
        RectorClassValidator $rectorClassValidator,
        RectorClassNormalizer $rectorClassNormalizer
    ) {
        $this->rectorClassValidator = $rectorClassValidator;
        $this->rectorClassNormalizer = $rectorClassNormalizer;
    }

    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        if (! isset($configs[0])) {
            return;
        }

        $rectors = array_merge(...$configs);

        $rectors = $this->rectorClassNormalizer->normalizer($rectors);

        $this->rectorClassValidator->validate(array_keys($rectors));

        foreach ($rectors as $rectorClass => $arguments) {
            $rectorDefinition = $containerBuilder->autowire($rectorClass);
            if (count($arguments)) {
                $rectorDefinition->setArguments([$arguments]);
            }
        }
    }
}
