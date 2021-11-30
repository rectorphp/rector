<?php

declare(strict_types=1);

use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AttributeKeyToClassConstFetchRector::class)
        ->configure([
            new AttributeKeyToClassConstFetch('Doctrine\ORM\Mapping\Column', 'type', 'Doctrine\DBAL\Types\Types', [
                'string' => 'STRING',
            ]),
        ]);
};
