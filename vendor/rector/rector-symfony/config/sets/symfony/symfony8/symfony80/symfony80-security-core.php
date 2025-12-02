<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony80\Rector\Class_\RemoveEraseCredentialsRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RemoveEraseCredentialsRector::class]);
};
