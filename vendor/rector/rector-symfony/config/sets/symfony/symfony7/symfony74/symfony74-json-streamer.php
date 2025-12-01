<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.4.md#jsonstreamer
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'getNativeToStreamValueTransformer', 'getValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'getStreamToNativeValueTransformers', 'getValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withNativeToStreamValueTransformers', 'withValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withStreamToNativeValueTransformers', 'withValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withAdditionalNativeToStreamValueTransformer', 'withAdditionalValueTransformer'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withAdditionalStreamToNativeValueTransformer', 'withAdditionalValueTransformer')]);
};
