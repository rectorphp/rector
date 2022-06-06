<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector;
use RectorPrefix20220606\Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector;
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
