<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/8.1/UPGRADE-8.1.md#httpkernel
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\Component\HttpKernel\Bundle\BundleInterface' => 'Symfony\Component\DependencyInjection\Kernel\BundleInterface', 'Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass' => 'Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass', 'Symfony\Component\HttpKernel\Config\FileLocator' => 'Symfony\Component\DependencyInjection\Kernel\FileLocator', 'Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter' => 'Symfony\Component\DependencyInjection\ServicesResetter', 'Symfony\Component\HttpKernel\DependencyInjection\ServicesResetterInterface' => 'Symfony\Component\DependencyInjection\ServicesResetterInterface', 'Symfony\Component\HttpKernel\DependencyInjection\ResettableServicePass' => 'Symfony\Component\DependencyInjection\Compiler\ResettableServicePass', 'Symfony\Component\HttpKernel\DependencyInjection\Extension' => 'Symfony\Component\DependencyInjection\Extension\Extension']);
};
