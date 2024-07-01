<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    // the rule order matters, as its used in withTypeCoverageLevel() method
    // place the safest rules first, follow by more complex ones
    $rectorConfig->rules(TypeDeclarationLevel::RULES);
};
