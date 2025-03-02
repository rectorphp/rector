<?php

declare (strict_types=1);
namespace RectorPrefix202503;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
// @see https://github.com/symfony/symfony/blame/7.2/UPGRADE-7.2.md
return RectorConfig::configure()->withConfiguredRule(RenameClassRector::class, [
    // @see https://github.com/symfony/symfony/blob/7.2/UPGRADE-7.2.md#serializer
    'Symfony\\Component\\Serializer\\NameConverter\\AdvancedNameConverterInterface' => 'Symfony\\Component\\Serializer\\NameConverter\\NameConverterInterface',
]);
