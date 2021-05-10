<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Nette\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector;
use Rector\Nette\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector;
use Rector\Nette\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector;
use Rector\Nette\Rector\Assign\MakeGetComponentAssignAnnotatedRector;
use Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector;
use Rector\Nette\Rector\Identical\SubstrMinusToStringEndsWithRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(TemplateMagicAssignToExplicitVariableArrayRector::class);
    $services->set(MakeGetComponentAssignAnnotatedRector::class);
    $services->set(AnnotateMagicalControlArrayAccessRector::class);
    $services->set(ArrayAccessSetControlToAddComponentMethodCallRector::class);
    $services->set(ArrayAccessGetControlToGetComponentMethodCallRector::class);
    $services->set(SubstrMinusToStringEndsWithRector::class);
};
