<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Nette\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector;
use Rector\Nette\Kdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector;
use Rector\Nette\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;
use Rector\Nette\Kdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(ChangeNetteEventNamesInGetSubscribedEventsRector::class);
    $services->set(ReplaceMagicPropertyEventWithEventClassRector::class);
    $services->set(ReplaceMagicPropertyWithEventClassRector::class);
    $services->set(ReplaceEventManagerWithEventSubscriberRector::class);
    $services->set(RenameClassRector::class)->call('configure', [[RenameClassRector::OLD_TO_NEW_CLASSES => ['Kdyby\\Events\\Subscriber' => 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface', 'Kdyby\\Events\\EventManager' => 'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface']]]);
};
