<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# source: https://book.cakephp.org/3.0/en/appendices/3-8-migration-guide.html

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $configuration = [new MethodCallRename('Cake\ORM\Entity', 'visibleProperties', 'getVisible')];

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => inline_objects($configuration),
        ]]);
};
