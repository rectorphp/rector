<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Rector\SymfonyPhpConfig\inline_value_objects;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Cake\Form\Form', 'errors', 'getErrors'),
                new MethodCallRename('Cake\Validation\Validation', 'cc', 'creditCard'),
                new MethodCallRename('Cake\Filesystem\Folder', 'normalizePath', 'correctSlashFor'),
                new MethodCallRename('Cake\Http\Client\Response', 'body', 'getStringBody'),
                new MethodCallRename('Cake\Core\Plugin', 'unload', 'clear'),
            ]),
        ]]);
};
