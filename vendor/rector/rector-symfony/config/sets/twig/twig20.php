<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return RectorConfig::configure()->withConfiguredRule(RenameClassRector::class, [
    #filters
    # @see https://twig.symfony.com/doc/1.x/deprecated.html
    'Twig_SimpleFilter' => 'Twig_Filter',
    #functions
    # @see https://twig.symfony.com/doc/1.x/deprecated.html
    'Twig_SimpleFunction' => 'Twig_Function',
    # @see https://github.com/bolt/bolt/pull/6596
    'Twig_SimpleTest' => 'Twig_Test',
]);
