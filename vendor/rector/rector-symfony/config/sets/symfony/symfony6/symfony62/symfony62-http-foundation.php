<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/47595
        'Symfony\\Component\\HttpFoundation\\ExpressionRequestMatcher' => 'Symfony\\Component\\HttpFoundation\\RequestMatcher\\ExpressionRequestMatcher',
        'Symfony\\Component\\HttpFoundation\\RequestMatcher' => 'Symfony\\Component\\HttpFoundation\\ChainRequestMatcher',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/45034
        new MethodCallRename('Symfony\\Component\\HttpFoundation\\Request', 'getContentType', 'getContentTypeFormat'),
    ]);
};
