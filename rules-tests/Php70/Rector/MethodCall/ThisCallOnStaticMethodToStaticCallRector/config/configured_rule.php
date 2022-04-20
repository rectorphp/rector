<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ThisCallOnStaticMethodToStaticCallRector::class);
};
