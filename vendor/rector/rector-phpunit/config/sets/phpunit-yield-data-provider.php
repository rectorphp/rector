<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ReturnArrayClassMethodToYieldRector::class, [new ReturnArrayClassMethodToYield('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'provide*'), new ReturnArrayClassMethodToYield('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'dataProvider*')]);
};
