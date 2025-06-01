<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(SecurityAttributeToIsGrantedAttributeRector::class);
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted' => 'Symfony\\Component\\Security\\Http\\Attribute\\IsGranted',
    ]);
};
