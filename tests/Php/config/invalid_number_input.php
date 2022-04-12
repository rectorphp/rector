<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, 291_084_902_184);
};
