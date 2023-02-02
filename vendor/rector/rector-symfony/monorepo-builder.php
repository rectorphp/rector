<?php

declare (strict_types=1);
namespace RectorPrefix202302;

use RectorPrefix202302\Symplify\MonorepoBuilder\Config\MBConfig;
use RectorPrefix202302\Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use RectorPrefix202302\Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
return static function (MBConfig $mbConfig) : void {
    // @see https://github.com/symplify/monorepo-builder#6-release-flow
    $mbConfig->workers([TagVersionReleaseWorker::class, PushTagReleaseWorker::class]);
};
