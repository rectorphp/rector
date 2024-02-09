<?php

declare (strict_types=1);
namespace RectorPrefix202402;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return RectorConfig::configure()->withImportNames(\true, \true, \true, \true)->withPaths([__DIR__ . '/src', __DIR__ . '/rules', __DIR__ . '/tests'])->withSkip(['*/Source/*', '*/Fixture/*'])->withRootFiles()->withPhpSets()->withPreparedSets(\true, \true, \true, \true, \true, \true)->withConfiguredRule(StringClassNameToClassConstantRector::class, ['Doctrine\\*', 'Gedmo\\*', 'Knp\\*', 'DateTime', 'DateTimeInterface']);
