<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;
use Rector\Transform\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Transform\ValueObject\PropertyAssignToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(PropertyAssignToMethodCallRector::class, [
            new PropertyAssignToMethodCall(ChoiceControl::class, 'checkAllowedValues', 'checkDefaultValue'),
        ]);
};
