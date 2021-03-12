<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename(AbstractType::class, 'setDefaultOptions', 'configureOptions'),
                new MethodCallRename('Nette\Utils\Html', 'add', 'addHtml'),
                new MethodCallRename(
                    'Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture\DemoFile',
                    'notify',
                    '__invoke'
                ),
                new MethodCallRename(
                    'Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture\SomeSubscriber',
                    'old',
                    'new'
                ),
                // with array key
                new MethodCallRenameWithArrayKey('Nette\Utils\Html', 'addToArray', 'addToHtmlArray', 'hey'),
            ]),
        ]]);
};
