<?php

declare (strict_types=1);
namespace RectorPrefix20210519;

use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210519\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set('namespace_typo3_cms_core_tests_to__typo3_testing_framework_core')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['RectorPrefix20210519\\TYPO3\\CMS\\Core\\Tests\\UnitTestCase' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Core\\Unit\\UnitTestCase', 'RectorPrefix20210519\\TYPO3\\CMS\\Core\\Tests\\FunctionalTestCase' => 'RectorPrefix20210519\\TYPO3\\TestingFramework\\Core\\Functional\\FunctionalTestCase']]]);
};
