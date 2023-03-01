<?php

declare (strict_types=1);
namespace RectorPrefix202303;

use Rector\Doctrine\Rector\Class_\ClassAnnotationToNamedArgumentConstructorRector;
use Rector\Doctrine\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector;
use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Rector\Doctrine\Rector\Property\DoctrineTargetEntityStringToClassConstantRector;
use RectorPrefix202303\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->paths([__DIR__ . '/config', __DIR__ . '/src']);
    $easyCIConfig->typesToSkip([\Rector\Core\Contract\Rector\RectorInterface::class]);
};
