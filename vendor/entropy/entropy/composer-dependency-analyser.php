<?php

// @see https://github.com/shipmonk-rnd/composer-dependency-analyser/
declare (strict_types=1);
namespace RectorPrefix202608;

use RectorPrefix202608\ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use RectorPrefix202608\ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
return (new Configuration())->ignoreErrorsOnExtension('ext-filter', [ErrorType::SHADOW_DEPENDENCY]);
