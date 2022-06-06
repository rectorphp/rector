<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\ParentClassToTraits;
// @see https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ParentClassToTraitsRector::class, [new ParentClassToTraits('Nette\\Object', ['Nette\\SmartObject'])]);
};
