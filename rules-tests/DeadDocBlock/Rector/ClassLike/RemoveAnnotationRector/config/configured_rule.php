<?php

use Rector\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector;
use Rector\Symfony\PhpDoc\Node\JMS\JMSInjectParamsTagValueNode;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveAnnotationRector::class)
        ->call('configure', [[
            RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => ['method', JMSInjectParamsTagValueNode::class],
        ]]);
};
