<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Rector\Nette\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(InitializeDefaultEntityCollectionRector::class);
    $rectorConfig->rule(RenderMethodParamToTypeDeclarationRector::class);
};
