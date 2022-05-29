<?php

declare (strict_types=1);
namespace RectorPrefix20220529;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector::class);
};