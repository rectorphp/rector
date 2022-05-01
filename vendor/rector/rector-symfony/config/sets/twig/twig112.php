<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
# see https://stackoverflow.com/a/43495506/1348344
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Twig_Function_Node' => 'Twig_SimpleFunction', 'Twig_Function' => 'Twig_SimpleFunction', 'Twig_Filter' => 'Twig_SimpleFilter', 'Twig_Test' => 'Twig_SimpleTest']);
};
