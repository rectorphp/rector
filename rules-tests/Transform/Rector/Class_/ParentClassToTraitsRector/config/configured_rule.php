<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Rector\Transform\ValueObject\ParentClassToTraits;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(ParentClassToTraitsRector::class, [
            new ParentClassToTraits(ParentObject::class, [SomeTrait::class]),
            new ParentClassToTraits(AnotherParentObject::class, [SomeTrait::class, SecondTrait::class]),
        ]);
};
