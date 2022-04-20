<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
};
