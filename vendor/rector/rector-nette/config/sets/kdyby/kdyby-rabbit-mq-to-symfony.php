<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Kdyby\\RabbitMq\\IConsumer', 'process', 'execute')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Kdyby\\RabbitMq\\IConsumer' => 'RectorPrefix20220607\\OldSound\\RabbitMqBundle\\RabbitMq\\ConsumerInterface', 'RectorPrefix20220607\\Kdyby\\RabbitMq\\IProducer' => 'RectorPrefix20220607\\OldSound\\RabbitMqBundle\\RabbitMq\\ProducerInterface']);
};
