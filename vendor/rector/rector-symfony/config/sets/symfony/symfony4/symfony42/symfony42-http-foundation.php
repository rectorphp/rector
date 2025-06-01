<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Rector\ValueObject\MethodName;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(NewToStaticCallRector::class, [new NewToStaticCall('Symfony\\Component\\HttpFoundation\\Cookie', 'Symfony\\Component\\HttpFoundation\\Cookie', 'create')]);
    // https://github.com/symfony/symfony/commit/9493cfd5f2366dab19bbdde0d0291d0575454567
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\\Component\\HttpFoundation\\Cookie', MethodName::CONSTRUCT, 5, \false, null), new ReplaceArgumentDefaultValue('Symfony\\Component\\HttpFoundation\\Cookie', MethodName::CONSTRUCT, 8, null, 'lax')]);
};
