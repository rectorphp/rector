<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod\ArgumentValueResolverToValueResolverRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/pull/47363
        ArgumentValueResolverToValueResolverRector::class,
    ]);
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46880
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache' => 'Symfony\\Component\\HttpKernel\\Attribute\\Cache',
        // @see https://github.com/symfony/symfony/pull/47363
        'Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface' => 'Symfony\\Component\\HttpKernel\\Controller\\ValueResolverInterface',
    ]);
};
