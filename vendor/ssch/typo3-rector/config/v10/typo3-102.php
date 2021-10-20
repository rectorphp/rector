<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Ssch\TYPO3Rector\Rector\v10\v2\ExcludeServiceKeysToArrayRector;
use Ssch\TYPO3Rector\Rector\v10\v2\InjectEnvironmentServiceIfNeededInResponseRector;
use Ssch\TYPO3Rector\Rector\v10\v2\MoveApplicationContextToEnvironmentApiRector;
use Ssch\TYPO3Rector\Rector\v10\v2\UseActionControllerRector;
use Ssch\TYPO3Rector\Rector\v10\v2\UseTypo3InformationForCopyRightNoticeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v2\MoveApplicationContextToEnvironmentApiRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v2\ExcludeServiceKeysToArrayRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v2\UseActionControllerRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v2\UseTypo3InformationForCopyRightNoticeRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v2\InjectEnvironmentServiceIfNeededInResponseRector::class);
};
