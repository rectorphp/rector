<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $rectorConfig->services();
    $services->set(AddProphecyTraitRector::class);
    $services->set(AddArrayParamDocTypeRector::class);
};
