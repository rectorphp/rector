<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.2/UPGRADE-7.2.md#serializer
        'Symfony\\Component\\Serializer\\NameConverter\\AdvancedNameConverterInterface' => 'Symfony\\Component\\Serializer\\NameConverter\\NameConverterInterface',
    ]);
};
