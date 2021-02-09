<?php

use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PseudoNamespaceToNamespaceRector::class)->call(
        'configure',
        [[
            PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => ValueObjectInliner::inline([

                new PseudoNamespaceToNamespace('PHPUnit_', ['PHPUnit_Framework_MockObject_MockObject']),
                new PseudoNamespaceToNamespace('ChangeMe_', ['KeepMe_']),
                new PseudoNamespaceToNamespace(
                    'Rector_Renaming_Tests_Rector_FileWithoutNamespace_PseudoNamespaceToNamespaceRector_Fixture_'
                ),

            ]),
        ]]
    );
};
