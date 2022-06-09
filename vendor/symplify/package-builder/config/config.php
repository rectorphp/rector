<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use RectorPrefix20220609\SebastianBergmann\Diff\Differ;
use RectorPrefix20220609\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220609\Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter;
use RectorPrefix20220609\Symplify\PackageBuilder\Console\Output\ConsoleDiffer;
use RectorPrefix20220609\Symplify\PackageBuilder\Diff\Output\CompleteUnifiedDiffOutputBuilderFactory;
use RectorPrefix20220609\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->set(ColorConsoleDiffFormatter::class);
    $services->set(ConsoleDiffer::class);
    $services->set(CompleteUnifiedDiffOutputBuilderFactory::class);
    $services->set(Differ::class);
    $services->set(PrivatesAccessor::class);
};
