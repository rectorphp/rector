<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::TYPED_PROPERTIES - 1);

    $services = $rectorConfig->services();
    $services->set(TypedPropertyFromToOneRelationTypeRector::class);
};
