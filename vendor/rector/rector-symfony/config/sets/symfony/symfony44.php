<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Symfony\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector;
# https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/symfony/symfony/pull/33775
    $rectorConfig->rule(ConsoleExecuteReturnIntRector::class);
    # https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md#security
    $rectorConfig->rule(AuthorizationCheckerIsGrantedExtractorRector::class);
};
