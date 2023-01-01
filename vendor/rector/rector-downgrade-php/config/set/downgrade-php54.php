<?php

declare (strict_types=1);
namespace RectorPrefix202301;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector;
use Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector;
use Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector;
use Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector;
use Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector;
use Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector;
use Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_53);
    $rectorConfig->rule(ShortArrayToLongArrayRector::class);
    $rectorConfig->rule(DowngradeStaticClosureRector::class);
    $rectorConfig->rule(DowngradeIndirectCallByArrayRector::class);
    $rectorConfig->rule(DowngradeCallableTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeBinaryNotationRector::class);
    $rectorConfig->rule(DowngradeInstanceMethodCallRector::class);
    $rectorConfig->rule(DowngradeThisInClosureRector::class);
};
