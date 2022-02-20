<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\ValueObjectInliner\config;

use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\Configuration\ValueObjectInliner;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\ServiceWithValueObject;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\WithType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $unionType = new UnionType([new StringType(), new NullType()]);

    $services->set(ServiceWithValueObject::class)
        ->call('setWithType', [ValueObjectInliner::inline(new WithType($unionType))]);
};
