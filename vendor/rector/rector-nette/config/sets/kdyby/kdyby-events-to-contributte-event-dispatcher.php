<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\Visibility;
use Rector\Nette\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector;
use Rector\Nette\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Nette\Kdyby\Rector\ClassMethod\ChangeNetteEventNamesInGetSubscribedEventsRector::class);
    $rectorConfig->rule(\Rector\Nette\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector::class, [new \Rector\Visibility\ValueObject\ChangeMethodVisibility('Kdyby\\Events\\Subscriber', 'getSubscribedEvents', \Rector\Core\ValueObject\Visibility::STATIC)]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Kdyby\\Events\\Subscriber' => 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface', 'Kdyby\\Events\\EventManager' => 'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface']);
};
