<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\ValueObjectInliner\config;

use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\ValueObjectInliner;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\ServiceWithValueObject;
use Rector\Core\Tests\Configuration\ValueObjectInliner\Source\WithType;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $unionType = new UnionType([new StringType(), new NullType()]);

    $services->set(ServiceWithValueObject::class)
        ->call('setWithType', [ValueObjectInliner::inline(new WithType($unionType))]);
};
