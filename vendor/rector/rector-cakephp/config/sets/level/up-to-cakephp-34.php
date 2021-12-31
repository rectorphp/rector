<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\CakePHP\Set\CakePHPSetList::CAKEPHP_30);
    $containerConfigurator->import(\Rector\CakePHP\Set\CakePHPSetList::CAKEPHP_34);
};
