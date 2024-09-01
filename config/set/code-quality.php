<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\Level\CodeQualityLevel;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    foreach (CodeQualityLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
        $rectorConfig->ruleWithConfiguration($rectorClass, $configuration);
    }
    // the rule order matters, as its used in withCodeQualityLevel() method
    // place the safest rules first, follow by more complex ones
    $rectorConfig->rules(CodeQualityLevel::RULES);
};
