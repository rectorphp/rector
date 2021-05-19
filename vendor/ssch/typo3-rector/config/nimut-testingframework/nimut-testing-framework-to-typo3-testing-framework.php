<?php

declare (strict_types=1);
namespace RectorPrefix20210519;

use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set('nimut_testing_framework_to_typo3_testing_framework')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['RectorPrefix20210519\\Nimut\\TestingFramework\\TestCase\\UnitTestCase' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Core\\Unit\\UnitTestCase', 'RectorPrefix20210519\\Nimut\\TestingFramework\\TestCase\\FunctionalTestCase' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Core\\Functional\\FunctionalTestCase', 'RectorPrefix20210519\\Nimut\\TestingFramework\\TestCase\\ViewHelperBaseTestcase' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Fluid\\Unit\\ViewHelpers\\ViewHelperBaseTestcase', 'RectorPrefix20210519\\Nimut\\TestingFramework\\MockObject\\AccessibleMockObjectInterface' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Core\\AccessibleObjectInterface', 'RectorPrefix20210519\\Nimut\\TestingFramework\\Exception\\Exception' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Core\\Exception']]]);
};
