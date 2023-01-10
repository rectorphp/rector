<?php

declare (strict_types=1);
namespace RectorPrefix202301;

use RectorPrefix202301\Symplify\MonorepoBuilder\Config\MBConfig;
use RectorPrefix202301\Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use RectorPrefix202301\Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
return static function (MBConfig $mbConfig) : void {
    // @see https://github.com/symplify/monorepo-builder#6-release-flow
    $mbConfig->workers([TagVersionReleaseWorker::class, PushTagReleaseWorker::class]);
};
