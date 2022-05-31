<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Rector\Transform\ValueObject\ParentClassToTraits;
// @see https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\Class_\ParentClassToTraitsRector::class, [new \Rector\Transform\ValueObject\ParentClassToTraits('Nette\\Object', ['Nette\\SmartObject'])]);
};
