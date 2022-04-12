<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RepeatedLiteralToClassConstantRector::class);
    $services->set(ReturnTypeDeclarationRector::class);

    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
};
