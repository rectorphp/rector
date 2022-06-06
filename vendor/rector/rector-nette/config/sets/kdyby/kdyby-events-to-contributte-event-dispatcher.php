<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\Visibility;
use RectorPrefix20220606\Rector\Nette\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector;
use RectorPrefix20220606\Rector\Nette\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use RectorPrefix20220606\Rector\Visibility\ValueObject\ChangeMethodVisibility;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ChangeNetteEventNamesInGetSubscribedEventsRector::class);
    $rectorConfig->rule(ReplaceEventManagerWithEventSubscriberRector::class);
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Kdyby\\Events\\Subscriber', 'getSubscribedEvents', Visibility::STATIC)]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Kdyby\\Events\\Subscriber' => 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface', 'Kdyby\\Events\\EventManager' => 'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface']);
};
