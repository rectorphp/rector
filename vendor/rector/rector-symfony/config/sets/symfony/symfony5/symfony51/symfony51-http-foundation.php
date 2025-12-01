<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\StaticCallToNew;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew('Symfony\Component\HttpFoundation\Response', 'create'), new StaticCallToNew('Symfony\Component\HttpFoundation\JsonResponse', 'create'), new StaticCallToNew('Symfony\Component\HttpFoundation\RedirectResponse', 'create'), new StaticCallToNew('Symfony\Component\HttpFoundation\StreamedResponse', 'create')]);
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'none', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'lax', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'strict', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
