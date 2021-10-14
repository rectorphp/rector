<?php

declare(strict_types=1);

use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AttributeKeyToClassConstFetchRector::class)
        ->call('configure', [[
            AttributeKeyToClassConstFetchRector::ATTRIBUTE_KEYS_TO_CLASS_CONST_FETCHES => ValueObjectInliner::inline([
                new AttributeKeyToClassConstFetch('Doctrine\ORM\Mapping\Column', 'type', 'Doctrine\DBAL\Types\Types', [
                    'string' => 'STRING',
                ]),
            ]),
        ]]);
};
