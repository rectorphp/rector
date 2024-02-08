<?php

declare (strict_types=1);
namespace RectorPrefix202402;

use Rector\Config\RectorConfig;
return RectorConfig::configure()->withPreparedSets(\true, \true, \true, \false, \true, \true, \false, \true)->withPaths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/tests'])->withImportNames(\true, \true, \true, \true)->withPhpSets()->withSkip(['*/Source/*']);
