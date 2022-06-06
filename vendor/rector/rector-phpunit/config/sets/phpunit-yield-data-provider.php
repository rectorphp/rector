<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use RectorPrefix20220606\Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use RectorPrefix20220606\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ReturnArrayClassMethodToYieldRector::class, [new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')]);
};
