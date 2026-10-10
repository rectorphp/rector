<?php

declare (strict_types=1);
namespace RectorPrefix202610;

use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Class_\RemoveRefactorDuplicatedNodeInstanceCheckRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\AddParamTypeToRefactorMethodRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([PostIncDecToPreIncDecRector::class, FinalizeTestCaseClassRector::class, AddParamTypeToRefactorMethodRector::class, RemoveRefactorDuplicatedNodeInstanceCheckRector::class, AddSeeTestAnnotationRector::class]);
};
