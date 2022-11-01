<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ReturnArrayClassMethodToYieldRector::class, [new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')]);
};
