<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Nette\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Nette\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector::class);
};
