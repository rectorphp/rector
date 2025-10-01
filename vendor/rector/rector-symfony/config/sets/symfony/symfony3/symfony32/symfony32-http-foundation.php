<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'lax', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'strict', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
