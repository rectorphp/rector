<?php

declare (strict_types=1);
namespace RectorPrefix202406;

use Rector\CodeQuality\Rector\FuncCall\BoolvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\FloatvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\StrvalToTypeCastRector;
use Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([DeclareStrictTypesRector::class, IntvalToTypeCastRector::class, StrvalToTypeCastRector::class, BoolvalToTypeCastRector::class, FloatvalToTypeCastRector::class, PostIncDecToPreIncDecRector::class, UseIncrementAssignRector::class, FinalizeTestCaseClassRector::class]);
};
