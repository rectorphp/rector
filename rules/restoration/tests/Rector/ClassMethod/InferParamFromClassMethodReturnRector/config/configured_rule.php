<?php

declare(strict_types=1);

use Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector;
use Rector\Restoration\Tests\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $configuration = ValueObjectInliner::inline([
        new InferParamFromClassMethodReturn(SomeType::class, 'process', 'getNodeTypes'),
    ]);

    $services->set(InferParamFromClassMethodReturnRector::class)
        ->call('configure', [[
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
            InferParamFromClassMethodReturnRector::PARAM_FROM_CLASS_METHOD_RETURNS => $configuration,
=======
            InferParamFromClassMethodReturnRector::INFER_PARAMS_FROM_CLASS_METHOD_RETURNS => ValueObjectInliner::inline([
                
=======
            InferParamFromClassMethodReturnRector::PARAM_FROM_CLASS_METHOD_RETURNS => ValueObjectInliner::inline([
>>>>>>> 495b7788a... use more configs
                new InferParamFromClassMethodReturn(SomeType::class, 'process', 'getNodeTypes'),
            ]),
>>>>>>> bb46bb10f... use config instead of setParameter()
=======
            InferParamFromClassMethodReturnRector::PARAM_FROM_CLASS_METHOD_RETURNS => $configuration,
>>>>>>> b1833ea1a... use php version right in config
        ]]);
};
