<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(ReturnArrayClassMethodToYieldRector::class)->call('configure', [[ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => ValueObjectInliner::inline([new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')])]]);
};
