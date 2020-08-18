<?php

declare(strict_types=1);

use Rector\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TernaryToElvisRector::class);

    $services->set(DirNameFileConstantToDirConstantRector::class);

    $services->set(ClearReturnNewByReferenceRector::class);

    $services->set(ReplaceHttpServerVarsByServerRector::class);
};
