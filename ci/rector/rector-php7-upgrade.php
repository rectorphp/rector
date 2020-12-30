<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::PHP_80,
        SetList::PHP_74,
        SetList::PHP_73,
        SetList::PHP_72,
        SetList::PHP_71,
        SetList::PHP_70,
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/tests'
    ]);

    $parameters->set(Option::SKIP, [
        '*/vendor/*'
    ]);
};
