<?php

declare(strict_types=1);

use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call('configure', [[
            ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => ValueObjectInliner::inline([
                new ServiceGetterToConstructorInjection(
                    'Doctrine\Common\Persistence\ManagerRegistry',
                    'getConnection',
                    'Doctrine\DBAL\Connection'
                ),
                new ServiceGetterToConstructorInjection(
                    'Doctrine\ORM\EntityManagerInterface',
                    'getConfiguration',
                    'Doctrine\ORM\Configuration'
                ),
            ]),
        ]]);
};
