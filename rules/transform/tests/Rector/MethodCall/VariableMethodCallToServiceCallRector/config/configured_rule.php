<?php

declare(strict_types=1);

use Rector\Transform\Rector\MethodCall\VariableMethodCallToServiceCallRector;
use Rector\Transform\ValueObject\VariableMethodCallToServiceCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
<<<<<<< HEAD
<<<<<<< HEAD

    $configuration = ValueObjectInliner::inline([
        new VariableMethodCallToServiceCall(
            'PhpParser\Node',
            'getAttribute',
            'php_doc_info',
            'Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory',
            'createFromNodeOrEmpty'
        ),
    ]);

    $services->set(VariableMethodCallToServiceCallRector::class)
        ->call('configure', [[
            VariableMethodCallToServiceCallRector::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => $configuration,
=======
    $services->set(VariableMethodCallToServiceCallRector::class)->call(
        'configure',
        [[
            VariableMethodCallToServiceCallRector::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => ValueObjectInliner::inline([
                
=======
>>>>>>> 495b7788a... use more configs

    $configuration = ValueObjectInliner::inline([
        new VariableMethodCallToServiceCall(
            'PhpParser\Node',
            'getAttribute',
            'php_doc_info',
            'Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory',
            'createFromNodeOrEmpty'
        ),
    ]);

<<<<<<< HEAD
                
            ]),
>>>>>>> bb46bb10f... use config instead of setParameter()
=======
    $services->set(VariableMethodCallToServiceCallRector::class)
        ->call('configure', [[
            VariableMethodCallToServiceCallRector::VARIABLE_METHOD_CALLS_TO_SERVICE_CALLS => $configuration,
>>>>>>> 495b7788a... use more configs
        ]]
    );
};
