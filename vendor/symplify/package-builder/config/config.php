<?php

declare (strict_types=1);
namespace RectorPrefix20220505;

use RectorPrefix20220505\SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220505\Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter;
use RectorPrefix20220505\Symplify\PackageBuilder\Console\Output\ConsoleDiffer;
use RectorPrefix20220505\Symplify\PackageBuilder\Diff\Output\CompleteUnifiedDiffOutputBuilderFactory;
use RectorPrefix20220505\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->set(\RectorPrefix20220505\Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter::class);
    $services->set(\RectorPrefix20220505\Symplify\PackageBuilder\Console\Output\ConsoleDiffer::class);
    $services->set(\RectorPrefix20220505\Symplify\PackageBuilder\Diff\Output\CompleteUnifiedDiffOutputBuilderFactory::class);
    $services->set(\RectorPrefix20220505\SebastianBergmann\Diff\Differ::class);
    $services->set(\RectorPrefix20220505\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
