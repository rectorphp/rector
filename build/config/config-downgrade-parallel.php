<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/config-downgrade.php');
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PARALLEL, true);
};
