<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony43\Rector\ClassMethod\EventDispatcherParentConstructRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // has lowest priority, have to be last
        'Symfony\Component\EventDispatcher\Event' => 'Symfony\Contracts\EventDispatcher\Event',
    ]);
    $rectorConfig->rules([MakeDispatchFirstArgumentEventRector::class, EventDispatcherParentConstructRector::class]);
};
