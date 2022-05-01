<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector::class);
};
