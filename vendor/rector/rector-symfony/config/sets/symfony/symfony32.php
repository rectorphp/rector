<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class)->configure([new \Rector\Arguments\ValueObject\ArgumentAdder('Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0)]);
};
