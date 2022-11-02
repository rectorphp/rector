<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted' => 'Symfony\\Component\\Security\\Http\\Attribute\\IsGranted',
        // @see https://github.com/symfony/symfony/pull/46880
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache' => 'Symfony\\Component\\HttpKernel\\Attribute\\Cache',
        // @see https://github.com/symfony/symfony/pull/46906
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template' => 'Symfony\\Bridge\\Twig\\Attribute\\Template',
    ]);
};
