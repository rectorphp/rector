<?php

declare(strict_types=1);

use Rector\Generic\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Generic\ValueObject\MethodCallToService;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call('configure', [[
            ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => inline_value_objects([
                new MethodCallToService(
                    'Doctrine\Common\Persistence\ManagerRegistry',
                    'getConnection',
                    'Doctrine\DBAL\Connection'
                ),
                new MethodCallToService(
                    'Doctrine\ORM\EntityManagerInterface',
                    'getConfiguration',
                    'Doctrine\ORM\Configuration'
                ),
            ]),
        ]]);
};
