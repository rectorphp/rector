<?php declare(strict_types=1);

namespace Rector\DependencyInjection\Extension;

use Rector\Validator\RectorClassValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class RectorsExtension extends Extension
{
    /**
     * @var RectorClassValidator
     */
    private $rectorClassValidator;

    public function __construct(RectorClassValidator $rectorClassValidator)
    {
        $this->rectorClassValidator = $rectorClassValidator;
    }

    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        if (! isset($configs[0])) {
            return;
        }

        $rectorClasses = $configs[0];

        $this->rectorClassValidator->validate($rectorClasses);

        foreach ($rectorClasses as $rectorClass) {
            $containerBuilder->autowire($rectorClass);
        }
    }
}
