<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Rector\ValueObject\MethodName;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(NewToStaticCallRector::class, [new NewToStaticCall('Symfony\Component\HttpFoundation\Cookie', 'Symfony\Component\HttpFoundation\Cookie', 'create')]);
    // https://github.com/symfony/symfony/commit/9493cfd5f2366dab19bbdde0d0291d0575454567
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', MethodName::CONSTRUCT, 5, \false, null), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', MethodName::CONSTRUCT, 8, null, 'lax'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'none', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'create', 8, 'none', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'create', 8, 'lax', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'create', 8, 'strict', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
