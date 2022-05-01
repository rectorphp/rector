<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Kdyby\\RabbitMq\\IConsumer', 'process', 'execute')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Kdyby\\RabbitMq\\IConsumer' => 'OldSound\\RabbitMqBundle\\RabbitMq\\ConsumerInterface', 'Kdyby\\RabbitMq\\IProducer' => 'OldSound\\RabbitMqBundle\\RabbitMq\\ProducerInterface']);
};
