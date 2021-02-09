<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class)->call('configure', [[
        \Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Renaming\ValueObject\RenameStaticMethod(
                \Nette\Utils\Html::class,
                'add',
                \Nette\Utils\Html::class,
                'addHtml'
            ),
            new \Rector\Renaming\ValueObject\RenameStaticMethod(
                \Rector\Renaming\Tests\Rector\StaticCall\RenameStaticMethodRector\Source\FormMacros::class,
                'renderFormBegin',
                'Nette\Bridges\FormsLatte\Runtime',
                'renderFormBegin'
            ),
























            
        ]),
    ]]);
};
