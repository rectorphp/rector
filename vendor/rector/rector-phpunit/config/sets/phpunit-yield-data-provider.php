<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector::class, [new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')]);
};
