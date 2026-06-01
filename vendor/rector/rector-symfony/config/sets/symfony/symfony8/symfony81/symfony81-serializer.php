<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/blob/8.1/UPGRADE-8.1.md#serializer
        new MethodCallRename('Symfony\Component\Serializer\Exception\PartialDenormalizationException', 'getErrors', 'getNotNormalizableValueErrors'),
    ]);
};
