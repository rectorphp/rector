<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Nette\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector;
use Rector\Nette\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ChangeNetteEventNamesInGetSubscribedEventsRector::class);
    $rectorConfig->rule(ReplaceEventManagerWithEventSubscriberRector::class);
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('RectorPrefix20220607\\Kdyby\\Events\\Subscriber', 'getSubscribedEvents', Visibility::STATIC)]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Kdyby\\Events\\Subscriber' => 'RectorPrefix20220607\\Symfony\\Component\\EventDispatcher\\EventSubscriberInterface', 'RectorPrefix20220607\\Kdyby\\Events\\EventManager' => 'RectorPrefix20220607\\Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface']);
};
