<?php

use Nette\Utils\Html;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\Tests\Rector\StaticCall\RenameStaticMethodRector\Source\FormMacros;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameStaticMethodRector::class)->call('configure', [[
        RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => ValueObjectInliner::inline([

            new RenameStaticMethod(Html::class, 'add', Html::class, 'addHtml'),
            new RenameStaticMethod(
                FormMacros::class,
                'renderFormBegin',
                'Nette\Bridges\FormsLatte\Runtime',
                'renderFormBegin'
            ),

        ]),
    ]]);
};
