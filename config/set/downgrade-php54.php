<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector;
use Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector;
use Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector;
use Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector;
use Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector;
use Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector;
use Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_53);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector::class);
};
