<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use RectorPrefix20220527\SebastianBergmann\Diff\Differ;
use RectorPrefix20220527\SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use RectorPrefix20220527\Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220527\Symplify\PackageBuilder\Composer\VendorDirProvider;
use RectorPrefix20220527\Symplify\PackageBuilder\Yaml\ParametersMerger;
use RectorPrefix20220527\Symplify\SmartFileSystem\Json\JsonFileSystem;
use RectorPrefix20220527\Symplify\VendorPatches\Console\VendorPatchesApplication;
use function RectorPrefix20220527\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20220527\Symplify\\VendorPatches\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject']);
    $services->set(UnifiedDiffOutputBuilder::class)->args(['$addLineNumbers' => \true]);
    $services->set(Differ::class)->args(['$outputBuilder' => service(UnifiedDiffOutputBuilder::class)]);
    $services->set(VendorDirProvider::class);
    $services->set(JsonFileSystem::class);
    // for autowired commands
    $services->alias(Application::class, VendorPatchesApplication::class);
    $services->set(ParametersMerger::class);
};
