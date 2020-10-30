<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions\config;

use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use function Rector\SymfonyPhpConfig\inline_value_object;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\ServiceWithValueObject;
use Rector\SymfonyPhpConfig\Tests\Functions\Source\WithType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $unionType = new UnionType([new StringType(), new NullType()]);

    $services->set(ServiceWithValueObject::class)
        ->call('setWithType', [inline_value_object(new WithType($unionType))]);
};
