<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Kdyby\\RabbitMq\\IConsumer', 'process', 'execute')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Kdyby\\RabbitMq\\IConsumer' => 'OldSound\\RabbitMqBundle\\RabbitMq\\ConsumerInterface', 'Kdyby\\RabbitMq\\IProducer' => 'OldSound\\RabbitMqBundle\\RabbitMq\\ProducerInterface']);
};
