<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\OrderAttributesRector;
use Rector\Config\RectorConfig;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\Source\SecondAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(OrderAttributesRector::class, [FirstAttribute::class, SecondAttribute::class]);
};
