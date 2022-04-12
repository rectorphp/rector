<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Rector\ChangeTextRector;
use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\TextFileProcessor;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->set(TextFileProcessor::class);
    $services->set(ChangeTextRector::class);
};
