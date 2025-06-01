<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\Level\CodingStyleLevel;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    foreach (CodingStyleLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
        $rectorConfig->ruleWithConfiguration($rectorClass, $configuration);
    }
    // the rule order matters, as its used in withCodingStyleLevel() method
    // place the safest rules first, follow by more complex ones
    $rectorConfig->rules(CodingStyleLevel::RULES);
};
