<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use RectorPrefix202608\Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()->withPaths([__DIR__ . '/bin', __DIR__ . '/src', __DIR__ . '/tests'])->withSkip([
    // invalid syntax test fixture
    __DIR__ . '/tests/UseImportsResolver/Fixture/ParseError.php',
])->withPreparedSets(\true, \true, \true);
