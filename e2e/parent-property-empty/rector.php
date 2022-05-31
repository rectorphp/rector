<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src/',
    ]);

    $rectorConfig->ruleWithConfiguration(
        StaticCallToMethodCallRector::class,
        [new StaticCallToMethodCall('Nette\Security\Passwords', 'hash', 'Nette\Security\Passwords', 'hash')]
    );
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Nextras\FormComponents\Controls\DatePicker' => 'Nextras\FormComponents\Controls\DateControl',
    ]);
};
