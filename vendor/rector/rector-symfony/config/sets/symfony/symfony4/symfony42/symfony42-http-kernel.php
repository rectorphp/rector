<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\ValueObject\MethodName;
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/symfony/symfony/commit/f5c355e1ba399a1b3512367647d902148bdaf09f
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Symfony\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector', MethodName::CONSTRUCT, 0, null), new ArgumentRemover('Symfony\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector', MethodName::CONSTRUCT, 1, null)]);
};
