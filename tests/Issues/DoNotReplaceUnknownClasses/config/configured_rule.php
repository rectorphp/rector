<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Property\DoctrineTargetEntityStringToClassConstantRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DoctrineTargetEntityStringToClassConstantRector::class);
    $rectorConfig->rule(TypedPropertyFromAssignsRector::class);
    $rectorConfig->rule(ReturnTypeDeclarationRector::class);
};
