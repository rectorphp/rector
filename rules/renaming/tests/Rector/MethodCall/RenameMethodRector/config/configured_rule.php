<?php

use Nette\Utils\Html;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameMethodRector::class)->call('configure', [[
        RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
            new MethodCallRename(AbstractType::class, 'setDefaultOptions', 'configureOptions'),
            new MethodCallRename(Html::class, 'add', 'addHtml'),
            new MethodCallRename(
                'Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture\DemoFile',
                'notify',
                '__invoke'
            ),
            new MethodCallRename('*Presenter', 'run', '__invoke'),
            new MethodCallRename(
                \Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture\SkipSelfMethodRename::class,
                'preventPHPStormRefactoring',
                'gone'
            ),
            // with array key
            new MethodCallRenameWithArrayKey(Html::class, 'addToArray', 'addToHtmlArray', 'hey'),
        ]),
    ]]);
};
