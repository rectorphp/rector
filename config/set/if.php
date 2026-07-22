<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector;
use Rector\CodeQuality\Rector\If_\ArrayExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\CompleteMissingIfElseBracketRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\If_\AlternativeIfToBracketRector;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AlternativeIfToBracketRector::class, CompleteMissingIfElseBracketRector::class, InlineIfToExplicitIfRector::class, TernaryFalseExpressionToIfRector::class, ArrayExplicitBoolCompareRector::class, ObjectExplicitBoolCompareRector::class, ExplicitBoolCompareRector::class, CombineIfRector::class, ShortenElseIfRector::class, SimplifyIfElseToTernaryRector::class]);
};
