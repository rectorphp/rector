<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

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
    $services = $rectorConfig->services();
    $services->set(\Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector::class);
};
