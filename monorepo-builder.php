<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;

return static function (MBConfig $mbConfig): void {
    // @see https://github.com/symplify/monorepo-builder#6-release-flow
    $mbConfig->workers([
        TagVersionReleaseWorker::class,
        PushTagReleaseWorker::class,
    ]);
};
