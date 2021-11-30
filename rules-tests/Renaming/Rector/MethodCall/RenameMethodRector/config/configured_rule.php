<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\CustomType;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\Foo;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\SomeSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameMethodRector::class)
        ->configure([
            new MethodCallRename(AbstractType::class, 'setDefaultOptions', 'configureOptions'),
            new MethodCallRename('Nette\Utils\Html', 'add', 'addHtml'),
            new MethodCallRename(CustomType::class, 'notify', '__invoke'),
            new MethodCallRename(SomeSubscriber::class, 'old', 'new'),
            new MethodCallRename(Foo::class, 'old', 'new'),
            // with array key
            new MethodCallRenameWithArrayKey('Nette\Utils\Html', 'addToArray', 'addToHtmlArray', 'hey'),
        ]);
};
