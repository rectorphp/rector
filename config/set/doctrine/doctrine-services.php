<?php

declare(strict_types=1);

use Rector\Core\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->arg('$methodNamesByTypesToServiceTypes', [
            'Doctrine\Common\Persistence\ManagerRegistry' => [
                'getConnection' => 'Doctrine\DBAL\Connection',
            ],
            'Doctrine\ORM\EntityManagerInterface' => [
                'getConfiguration' => 'Doctrine\ORM\Configuration',
            ],
        ]);
};
