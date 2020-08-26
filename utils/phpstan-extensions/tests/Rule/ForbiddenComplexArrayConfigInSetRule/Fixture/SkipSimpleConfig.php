<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenComplexArrayConfigInSetRule\Fixture;

use Rector\Generic\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call('configure', [[
            ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => [
                'Doctrine\Common\Persistence\ManagerRegistry' => 'simple'
            ]
        ]]);
};
