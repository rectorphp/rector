<?php

declare(strict_types=1);

use Rector\Set\ValueObject\DowngradeSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(DowngradeSetList::PHP_71);
    $containerConfigurator->import(DowngradeSetList::PHP_72);
    $containerConfigurator->import(DowngradeSetList::PHP_73);
    $containerConfigurator->import(DowngradeSetList::PHP_74);
    $containerConfigurator->import(DowngradeSetList::PHP_80);
};
