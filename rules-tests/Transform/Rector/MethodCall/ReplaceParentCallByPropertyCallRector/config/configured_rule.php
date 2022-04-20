<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(ReplaceParentCallByPropertyCallRector::class, [
            new ReplaceParentCallByPropertyCall(TypeClassToReplaceMethodCallBy::class, 'someMethod', 'someProperty'),
        ]);
};
