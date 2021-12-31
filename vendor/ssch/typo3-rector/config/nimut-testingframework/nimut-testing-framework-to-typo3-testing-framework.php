<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Nimut\\TestingFramework\\TestCase\\UnitTestCase' => 'TYPO3\\TestingFramework\\Core\\Unit\\UnitTestCase', 'Nimut\\TestingFramework\\TestCase\\FunctionalTestCase' => 'TYPO3\\TestingFramework\\Core\\Functional\\FunctionalTestCase', 'Nimut\\TestingFramework\\TestCase\\ViewHelperBaseTestcase' => 'TYPO3\\TestingFramework\\Fluid\\Unit\\ViewHelpers\\ViewHelperBaseTestcase', 'Nimut\\TestingFramework\\MockObject\\AccessibleMockObjectInterface' => 'TYPO3\\TestingFramework\\Core\\AccessibleObjectInterface', 'Nimut\\TestingFramework\\Exception\\Exception' => 'TYPO3\\TestingFramework\\Core\\Exception']);
};
