<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v0\RemoveSeliconFieldPathRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v0\RemoveTcaOptionSetToDefaultOnCopyRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v3\RemoveExcludeOnTransOrigPointerFieldRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v3\RemoveShowRecordFieldListInsideInterfaceSectionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RemoveSeliconFieldPathRector::class);
    $rectorConfig->rule(RemoveTcaOptionSetToDefaultOnCopyRector::class);
    $rectorConfig->rule(RemoveExcludeOnTransOrigPointerFieldRector::class);
    $rectorConfig->rule(RemoveShowRecordFieldListInsideInterfaceSectionRector::class);
};
