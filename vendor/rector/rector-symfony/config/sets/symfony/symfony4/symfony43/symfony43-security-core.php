<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # Security
        'Symfony\\Component\\Security\\Core\\Encoder\\Argon2iPasswordEncoder' => 'Symfony\\Component\\Security\\Core\\Encoder\\SodiumPasswordEncoder',
        'Symfony\\Component\\Security\\Core\\Encoder\\BCryptPasswordEncoder' => 'Symfony\\Component\\Security\\Core\\Encoder\\NativePasswordEncoder',
    ]);
};
