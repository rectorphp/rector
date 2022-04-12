<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AttributeKeyToClassConstFetchRector::class)
        ->configure([
            new AttributeKeyToClassConstFetch('Doctrine\ORM\Mapping\Column', 'type', 'Doctrine\DBAL\Types\Types', [
                'string' => 'STRING',
            ]),
        ]);
};
