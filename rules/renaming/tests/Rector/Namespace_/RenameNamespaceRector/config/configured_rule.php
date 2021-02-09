<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\Namespace_\RenameNamespaceRector::class)->call('configure', [[
        \Rector\Renaming\Rector\Namespace_\RenameNamespaceRector::OLD_TO_NEW_NAMESPACES => [
            'OldNamespace' => 'NewNamespace',
            'OldNamespaceWith\OldSplitNamespace' => 'NewNamespaceWith\NewSplitNamespace',
            'Old\Long\AnyNamespace' => 'Short\AnyNamespace',
            'PHPUnit_Framework_' => 'PHPUnit\Framework\\',
        ],
    ]]);
};
