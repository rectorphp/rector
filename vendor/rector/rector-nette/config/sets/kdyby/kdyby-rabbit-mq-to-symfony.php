<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Kdyby\\RabbitMq\\IConsumer', 'process', 'execute')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Kdyby\\RabbitMq\\IConsumer' => 'OldSound\\RabbitMqBundle\\RabbitMq\\ConsumerInterface', 'Kdyby\\RabbitMq\\IProducer' => 'OldSound\\RabbitMqBundle\\RabbitMq\\ProducerInterface']);
};
