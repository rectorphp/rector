<?php

declare(strict_types=1);

use Rector\Composer\Rector\RenamePackageComposerRector;
use Rector\Composer\ValueObject\RenamePackage;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenamePackageComposerRector::class)
        ->configure([new RenamePackage('foo/bar', 'baz/bar'), new RenamePackage('foo/baz', 'baz/baz')]);
};
