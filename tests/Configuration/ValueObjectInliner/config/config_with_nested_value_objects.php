<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\ValueObjectInliner\config;

use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
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

    $withType = new WithType(new IntegerType());

    $services->set(ServiceWithValueObject::class)
        ->call('setWithType', [ValueObjectInliner::inline($withType)])
        ->call('setWithTypes', [ValueObjectInliner::inline([new WithType(new StringType())])]);
};
