<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# https://github.com/symfony/symfony/blob/5.0/UPGRADE-5.0.md
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/symfony50-types.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Symfony\\Component\\Debug\\Debug' => 'Symfony\\Component\\ErrorHandler\\Debug']);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Console\\Application', 'renderException', 'renderThrowable'), new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Console\\Application', 'doRenderException', 'doRenderThrowable')]);
};
