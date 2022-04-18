<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Ssch\TYPO3Rector\Rector\v10\v0\RemoveSeliconFieldPathRector;
use Ssch\TYPO3Rector\Rector\v10\v0\RemoveTcaOptionSetToDefaultOnCopyRector;
use Ssch\TYPO3Rector\Rector\v10\v3\RemoveExcludeOnTransOrigPointerFieldRector;
use Ssch\TYPO3Rector\Rector\v10\v3\RemoveShowRecordFieldListInsideInterfaceSectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v0\RemoveSeliconFieldPathRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v0\RemoveTcaOptionSetToDefaultOnCopyRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v3\RemoveExcludeOnTransOrigPointerFieldRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v3\RemoveShowRecordFieldListInsideInterfaceSectionRector::class);
};
