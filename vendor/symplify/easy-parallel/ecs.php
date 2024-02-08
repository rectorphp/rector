<?php

declare (strict_types=1);
namespace RectorPrefix202402;

use RectorPrefix202402\Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()->withPaths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/tests'])->withRootFiles()->withPreparedSets(common: \true, psr12: \true);
