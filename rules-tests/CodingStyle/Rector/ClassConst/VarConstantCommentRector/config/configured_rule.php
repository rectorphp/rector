<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(VarConstantCommentRector::class);
};
