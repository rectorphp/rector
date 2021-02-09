<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector::class)->call('configure', [[
        \Rector\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => [
            'method',
            \Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectParamsTagValueNode::class,
        ],
    ]]);
};
