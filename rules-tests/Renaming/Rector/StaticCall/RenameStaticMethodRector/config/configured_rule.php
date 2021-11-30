<?php

declare(strict_types=1);

use Nette\Utils\Html;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Tests\Renaming\Rector\StaticCall\RenameStaticMethodRector\Source\FormMacros;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameStaticMethodRector::class)
        ->configure([

            new RenameStaticMethod(Html::class, 'add', Html::class, 'addHtml'),
            new RenameStaticMethod(
                FormMacros::class,
                'renderFormBegin',
                'Nette\Bridges\FormsLatte\Runtime',
                'renderFormBegin'
            ),

        ]);
};
