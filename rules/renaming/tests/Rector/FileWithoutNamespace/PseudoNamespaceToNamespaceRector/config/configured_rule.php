<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector::class)->call(
        'configure',
        [[
            \Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace('PHPUnit_', [
                    'PHPUnit_Framework_MockObject_MockObject',
                ]),
                new \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace('ChangeMe_', ['KeepMe_']),
                new \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace(
                    'Rector_Renaming_Tests_Rector_FileWithoutNamespace_PseudoNamespaceToNamespaceRector_Fixture_'
                ),
























                
            ]),
        ]]
    );
};
