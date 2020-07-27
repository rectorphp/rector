<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->arg('$typehintForParameterByMethodByClass', [
            'Nette\ComponentModel\Component' => [
                'lookup' => ['?string', 'bool'],
                'detached' => ['Nette\ComponentModel\IComponent'],
                'link' => ['string']
            ],
        ]);

    $services->set(Command::class);
};
