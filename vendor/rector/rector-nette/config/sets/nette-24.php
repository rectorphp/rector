<?php

declare (strict_types=1);
namespace RectorPrefix20211207;

use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Rector\Transform\ValueObject\ParentClassToTraits;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20211207\Symplify\SymfonyPhpConfig\ValueObjectInliner;
// @see https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Class_\ParentClassToTraitsRector::class)->call('configure', [[\Rector\Transform\Rector\Class_\ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => \RectorPrefix20211207\Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\ParentClassToTraits('Nette\\Object', ['Nette\\SmartObject'])])]]);
};
