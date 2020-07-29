<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Sylius\Bundle\AdminApiBundle\Model\ClientManager' => [
                    # source: https://github.com/Sylius/Sylius/blob/master/UPGRADE-1.0.md#upgrade-from-101-to-102
                    'findClientByPublicId' => '?Sylius\Bundle\AdminApiBundle\Model\ClientInterface',
                ],
            ],
        ]]);
};
