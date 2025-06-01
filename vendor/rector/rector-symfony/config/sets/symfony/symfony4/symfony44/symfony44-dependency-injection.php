<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\tagged' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\tagged_iterator']);
};
