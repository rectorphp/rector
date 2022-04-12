<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::STATIC_RETURN_TYPE - 1);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/../../../../../../phpstan-for-rector.neon');

    $services = $rectorConfig->services();
    $services->set(ReturnTypeDeclarationRector::class);
};
