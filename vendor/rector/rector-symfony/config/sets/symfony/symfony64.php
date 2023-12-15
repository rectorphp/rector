<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
// @see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\HttpKernel\\UriSigner' => 'Symfony\\Component\\HttpFoundation\\UriSigner', 'Symfony\\Component\\HttpKernel\\Debug\\FileLinkFormatter' => 'Symfony\\Component\\ErrorHandler\\ErrorRenderer\\FileLinkFormatter']);
};
