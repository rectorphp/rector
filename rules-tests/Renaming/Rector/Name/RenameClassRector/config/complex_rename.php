<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClassWithNewMethod;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassWithMethod;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $services = $rectorConfig->services();
    $services->set(RenameClassRector::class)
        ->configure([
            OldClassWithMethod::class => NewClassWithNewMethod::class,
        ]);

    $services->set(RenameMethodRector::class)
        ->configure([new MethodCallRename(NewClassWithNewMethod::class, 'someMethod', 'someNewMethod')]);
};
