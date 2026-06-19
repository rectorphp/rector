<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use Rector\Utils\Rector\RemoveRefactorDuplicatedNodeInstanceCheckRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([DeclareStrictTypesRector::class, PostIncDecToPreIncDecRector::class, FinalizeTestCaseClassRector::class, RemoveRefactorDuplicatedNodeInstanceCheckRector::class, AddSeeTestAnnotationRector::class]);
};
